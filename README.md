# Digital Bookings

Digital Bookings is a web-based booking management system designed for La Sentinelle to manage billboard and digital advertising space reservations. The application provides an intuitive dashboard for tracking bookings, managing clients, salespeople, and billboard inventory.

## Features

- User authentication with secure session management
- Dashboard with upcoming bookings overview
- Billboard and advertising space management
- Client management
- Salespeople tracking
- Role-based access control

## Technology Stack

### Backend
- **PHP** 8.5
- **Laravel** 12
- **PostgreSQL/SQLite** for database

### Frontend
- **Tailwind CSS** 4
- **Alpine.js** 3
- **Vite** 7 for asset bundling

### Development Tools
- **Laravel Pint** for code formatting
- **Pest** 4 for testing

## Requirements

- PHP >= 8.5
- Composer
- Node.js >= 18
- PostgreSQL 15+ or SQLite
- Nginx or Apache

## Local Development

1. Clone the repository:
   ```bash
   git clone https://github.com/your-org/digital-bookings.git
   cd digital-bookings
   ```

2. Install PHP dependencies:
   ```bash
   composer install
   ```

3. Install Node.js dependencies:
   ```bash
   npm install
   ```

4. Copy the environment file and generate an application key:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. Configure your database in `.env`:
   ```env
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=digital_bookings
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

6. Run database migrations:
   ```bash
   php artisan migrate
   ```

7. Build frontend assets:
   ```bash
   npm run build
   ```

8. Start the development server:
   ```bash
   php artisan serve
   ```

## Deployment on a Linux VM

### 1. Server Preparation

Update system packages and install required software:

```bash
sudo apt update && sudo apt upgrade -y

# Install PHP 8.5 and required extensions
sudo apt install -y php8.5 php8.5-fpm php8.5-pgsql php8.5-mbstring php8.5-xml php8.5-curl php8.5-zip php8.5-bcmath php8.5-gd

# Install Nginx
sudo apt install -y nginx

# Install PostgreSQL
sudo apt install -y postgresql postgresql-contrib

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### 2. Create Database

```bash
sudo -u postgres psql
```

```sql
CREATE DATABASE digital_bookings;
CREATE USER digital_bookings WITH ENCRYPTED PASSWORD 'your_secure_password';
GRANT ALL PRIVILEGES ON DATABASE digital_bookings TO digital_bookings;
ALTER DATABASE digital_bookings OWNER TO digital_bookings;
\q
```

### 3. Deploy Application

```bash
# Create application directory
sudo mkdir -p /var/www/digital-bookings
sudo chown -R $USER:www-data /var/www/digital-bookings

# Clone or upload your application
cd /var/www/digital-bookings
git clone https://github.com/your-org/digital-bookings.git .

# Install dependencies
composer install --no-dev --optimize-autoloader
npm install
npm run build

# Configure environment
cp .env.example .env
php artisan key:generate

# Edit .env with production settings
nano .env
```

Update the `.env` file with production values:

```env
APP_NAME="Digital Bookings"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=digital_bookings
DB_USERNAME=digital_bookings
DB_PASSWORD=your_secure_password

SESSION_DRIVER=database
CACHE_DRIVER=file
QUEUE_CONNECTION=database
```

### 4. Set Permissions

```bash
sudo chown -R www-data:www-data /var/www/digital-bookings
sudo chmod -R 755 /var/www/digital-bookings
sudo chmod -R 775 /var/www/digital-bookings/storage
sudo chmod -R 775 /var/www/digital-bookings/bootstrap/cache
```

### 5. Run Migrations

```bash
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 6. Configure Nginx

Create a new Nginx configuration file:

```bash
sudo nano /etc/nginx/sites-available/digital-bookings
```

Add the following configuration:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com;
    root /var/www/digital-bookings/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.5-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable the site and restart Nginx:

```bash
sudo ln -s /etc/nginx/sites-available/digital-bookings /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### 7. Configure SSL (Recommended)

Install Certbot and obtain an SSL certificate:

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d your-domain.com
```

### 8. Set Up Queue Worker (Optional)

For background job processing, create a systemd service:

```bash
sudo nano /etc/systemd/system/digital-bookings-worker.service
```

Add the following:

```ini
[Unit]
Description=Digital Bookings Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/digital-bookings/artisan queue:work --sleep=3 --tries=3

[Install]
WantedBy=multi-user.target
```

Enable and start the service:

```bash
sudo systemctl enable digital-bookings-worker
sudo systemctl start digital-bookings-worker
```

### 9. Set Up Scheduler (Optional)

Add Laravel scheduler to crontab:

```bash
sudo crontab -e
```

Add the following line:

```
* * * * * cd /var/www/digital-bookings && php artisan schedule:run >> /dev/null 2>&1
```

## Running Tests

```bash
php artisan test
```

## Code Formatting

```bash
vendor/bin/pint
```

## Documentation

Project documentation lives in [`docs/`](./docs/README.md). Notable entries:

- [SAGE Export](./docs/sage-export.md) — how the SAGE accounting CSV export works (filters, row format, edge cases, worked example).

## License

This project is proprietary software owned by La Sentinelle.
