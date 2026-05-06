# Production deployment with FrankenPHP + Laravel Octane

Digital Bookings runs in production behind **Laravel Octane** with the **FrankenPHP** server in worker mode. FrankenPHP replaces the traditional Nginx + PHP-FPM pair with a single process that serves both static assets and PHP, with automatic HTTPS via embedded Caddy.

This document covers installation, the systemd unit, zero-downtime deploys, and the trade-offs of worker mode.

## Why this stack

| Stack | Throughput (rel.) | HTTPS | Process model | Notes |
|---|---|---|---|---|
| Nginx + PHP-FPM | 1× | Manual / Certbot | Boot Laravel per request | The classic deploy |
| FrankenPHP, classic mode | ~1.5× | Auto via Caddy | Boot Laravel per request | Drop-in PHP-FPM replacement |
| **FrankenPHP + Octane (worker mode)** | **~5×** | **Auto via Caddy** | **Persistent app, swap request state per call** | What we use |

Worker mode keeps a Laravel application booted across requests, so route compilation, container resolution, and config loading happen once at start-up — not on every request.

## Prerequisites

The openSUSE Leap 16.0 deployment guide in [`README.md`](../README.md#deployment-on-a-linux-vm) covers PHP 8.5, PostgreSQL, Node.js, and Composer. With those installed, FrankenPHP needs **no Nginx, no php-fpm, no separate Certbot.**

You do still need a system PHP 8.5 (FrankenPHP embeds its own PHP, but Composer + Artisan from the host PHP are how we install / migrate / cache).

## 1. Install the FrankenPHP binary

```bash
# Download the latest static binary to /usr/local/bin (no zypper package yet)
sudo curl -L https://github.com/php/frankenphp/releases/latest/download/frankenphp-linux-x86_64 \
     -o /usr/local/bin/frankenphp
sudo chmod +x /usr/local/bin/frankenphp

frankenphp version
```

The binary is ~170 MB — it ships its own PHP runtime (8.5), Caddy, and Brotli/Zstd. The Composer-pulled `frankenphp` binary inside the project root (created by `php artisan octane:install`) is for **local dev only** and is `.gitignore`'d.

## 2. Allow FrankenPHP to bind to ports 80 / 443

FrankenPHP serves directly on the standard HTTP(S) ports — no reverse proxy in front. On systemd-based hosts:

```bash
sudo setcap CAP_NET_BIND_SERVICE=+eip /usr/local/bin/frankenphp
```

This grants the binary permission to bind low-numbered ports without running as root.

## 3. Application setup on the server

```bash
cd /var/www/digital-bookings

# One-off, after `git pull`:
composer install --no-dev --optimize-autoloader --no-interaction
npm ci && npm run build

php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

Octane uses cached config / routes — re-run these caches whenever you deploy code changes.

In `.env`:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

OCTANE_SERVER=frankenphp
OCTANE_HTTPS=true
```

`OCTANE_HTTPS=true` makes Laravel emit `https://` URLs even though the worker speaks plain HTTP to the embedded Caddy. The handoff between Caddy and Octane is over a Unix socket.

## 4. systemd service

```bash
sudo nano /etc/systemd/system/digital-bookings.service
```

```ini
[Unit]
Description=Digital Bookings (Laravel Octane + FrankenPHP)
After=network.target postgresql.service

[Service]
Type=simple
User=www-data
Group=www-data
WorkingDirectory=/var/www/digital-bookings

# --workers: number of persistent Laravel workers (start with 2× CPU cores)
# --max-requests: recycle each worker after N requests to defend against memory leaks
# --host 0.0.0.0 --port 443 --https: bind directly to TLS, auto-issue via Let's Encrypt
ExecStart=/usr/local/bin/frankenphp php-server \
    --root /var/www/digital-bookings/public \
    --listen :443

# Use this instead if you go through Octane (worker mode):
# ExecStart=/usr/bin/php artisan octane:start \
#     --server=frankenphp \
#     --host=0.0.0.0 \
#     --port=443 \
#     --https \
#     --workers=8 \
#     --max-requests=500

ExecReload=/bin/kill -USR1 $MAINPID
Restart=always
RestartSec=3

# Security
NoNewPrivileges=true
ProtectSystem=strict
ReadWritePaths=/var/www/digital-bookings/storage /var/www/digital-bookings/bootstrap/cache
PrivateTmp=true

[Install]
WantedBy=multi-user.target
```

The `ExecStart` shown is **classic mode** — no Octane, just FrankenPHP serving PHP per request. Use this if you want the simplest possible setup.

For **worker mode** (the recommended production setup), comment out the classic line and uncomment the Octane line below it. Worker mode requires Octane to be installed (it is — see `composer.json`).

```bash
sudo systemctl daemon-reload
sudo systemctl enable --now digital-bookings
sudo systemctl status digital-bookings
```

The first request triggers Caddy's auto-TLS — within ~10 seconds Let's Encrypt issues a certificate and HTTPS becomes live. The cert is renewed automatically.

## 5. Zero-downtime deploys

```bash
cd /var/www/digital-bookings
git pull origin main

composer install --no-dev --optimize-autoloader --no-interaction
npm ci && npm run build

php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Reload — sends SIGUSR1, FrankenPHP gracefully restarts workers without
# dropping in-flight requests.
sudo systemctl reload digital-bookings
```

`reload` (SIGUSR1) is the right signal for code changes. Use `restart` only when changing the systemd unit itself.

## 6. Worker-mode constraints (worth knowing)

When the app is booted once and reused across requests, anything stateful between requests becomes a leak. Octane handles the common cases automatically (clears the request, response, auth, query log) but keep these in mind when adding code:

- **Service-provider singletons that capture request data** — register them as `bind`, not `singleton`, or reset them in `OctaneServiceProvider`.
- **Static caches inside helpers** (e.g. `private static $config`) — reset in a `RequestReceived` listener if they hold per-request state.
- **`Auth::user()` outside a request context** — Octane resets auth between requests, but background tasks need their own `Auth::shouldUse('api')`-style scoping.
- **Long-running queries / file handles** — close them; don't lean on PHP's per-request shutdown to do it for you.

Laravel's `Octane` listeners (see `config/octane.php`) already wire up `FlushTemporaryContainerInstances`, `DisconnectFromDatabases`, `CollectGarbage`, and `EnsureUploadedFilesAreValid`. The default config is good — only customise if you find a specific leak.

## 7. Logging and observability

FrankenPHP / Caddy logs go to journald by default:

```bash
sudo journalctl -u digital-bookings -f
```

Octane logs Laravel application errors there too. For application-level structured logging, the existing Laravel `LOG_CHANNEL=stack` config keeps writing to `storage/logs/laravel.log` as before.

## 8. Rolling back to Nginx + PHP-FPM (if needed)

If something blocks production on the new stack, you can fall back without code changes:

```bash
sudo systemctl stop digital-bookings
sudo systemctl disable digital-bookings
sudo systemctl enable --now nginx php-fpm
```

The Nginx config from the old `README.md` still works — Octane is opt-in and never required for the app to boot.

## File map

| Path | Role |
|---|---|
| `composer.json` | `laravel/octane` dependency |
| `config/octane.php` | Worker listeners, warm-up, garbage collection settings |
| `.env.example` | `OCTANE_SERVER=frankenphp`, `OCTANE_HTTPS=false` |
| `.gitignore` | Ignores the local `frankenphp` binary and `frankenphp-worker.php` (created by `php artisan octane:install`) |
| `docs/deployment-frankenphp.md` | This file |
| `/etc/systemd/system/digital-bookings.service` | Production unit (server-side, not in repo) |
