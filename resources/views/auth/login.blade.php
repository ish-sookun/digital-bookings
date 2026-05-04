<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="apple-touch-icon" href="/favicon.png">
    <title>Login • Digital Bookings</title>

    {{-- Apply stored theme before first paint to prevent FOUC --}}
    <script>
      (function () {
        try {
          var stored = localStorage.getItem('ls-theme');
          if (stored === 'dark' || stored === 'light') {
            document.documentElement.setAttribute('data-theme', stored);
          }
        } catch (e) {}
      })();
    </script>

    {{-- Styles / Scripts --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
  </head>

  <body class="min-h-screen bg-ls-page text-ls-text">
    <main class="min-h-screen flex items-center justify-center px-6 py-16">
      <div class="w-full max-w-md">
        <div class="flex items-center justify-center gap-3">
          <img src="/digital-bookings-logo.svg" alt="Digital Bookings" class="h-10 w-10" />
          <span class="text-xl font-bold tracking-tight text-ls-text">Digital Bookings</span>
        </div>

        {{-- Card starts --}}
        <div class="mt-10 rounded-2xl border border-ls-border-strong bg-ls-surface px-8 py-10 shadow-sm">
          <h1 class="text-center text-xl font-medium tracking-tight text-ls-text">
            Sign in to your account
          </h1>

          @if(session('error'))
            <div class="mt-6">
              <x-ls.alert variant="danger">{{ session('error') }}</x-ls.alert>
            </div>
          @endif

          <form class="mt-8 space-y-6" action="{{ route('login.store') }}" method="POST">
            @csrf

            <x-ls.input-field
              name="email"
              type="email"
              label="Email"
              :value="old('email')"
              autocomplete="email"
              required
            />

            <x-ls.input-field
              name="password"
              type="password"
              label="Password"
              autocomplete="current-password"
              required
            />

            <div class="flex items-center justify-between">
              <x-ls.check name="remember" :includeHidden="false" label="Remember me" />

              <a
                href="#"
                class="text-sm font-medium text-ls-text underline underline-offset-4 hover:text-ls-deep"
              >
                Forgot password?
              </a>
            </div>

            <x-ls.button type="submit" variant="primary" class="w-full">
              Login
            </x-ls.button>
          </form>
        </div>
        {{-- Card ends --}}
      </div>
    </main>
  </body>
</html>
