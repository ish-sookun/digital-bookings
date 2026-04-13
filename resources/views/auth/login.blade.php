
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="apple-touch-icon" href="/favicon.png">
    <title>Login • Digital Bookings</title>

    {{-- Styles / Scripts --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
  </head>

  <body class="min-h-screen bg-white">
    <main class="min-h-screen flex items-center justify-center px-6 py-16">
      <div class="w-full max-w-md">
        <div class="flex items-center justify-center gap-3">
          <img src="/digital-bookings-logo.svg" alt="Digital Bookings" class="h-10 w-10" />
          <span class="text-xl font-bold tracking-tight text-gray-900">Digital Bookings</span>
        </div>

        {{-- Card starts --}}
        <div class="mt-10 rounded-2xl border border-gray-200 bg-white px-8 py-10 shadow-sm">
          <h1 class="text-center text-xl font-semibold tracking-tight text-gray-900">
            Sign in to your account
          </h1>

          @if(session('error'))
            <div class="mt-6 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
              {{ session('error') }}
            </div>
          @endif

          <form class="mt-8 space-y-6" action="{{ route('login.store') }}" method="POST">
            @csrf
            <!-- Email -->
            <div>
              <label for="email" class="block text-sm font-medium text-gray-700">
                Email
              </label>
              <div class="mt-2">
                <input
                  id="email"
                  name="email"
                  type="email"
                  autocomplete="email"
                  required
                  value="{{ old('email') }}"
                  class="block w-full rounded-lg border @error('email') border-red-500 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-100"
                  placeholder=""
                />
              </div>
              @error('email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
              @enderror
            </div>

            <!-- Password -->
            <div>
              <label for="password" class="block text-sm font-medium text-gray-700">
                Password
              </label>
              <div class="mt-2">
                <input
                  id="password"
                  name="password"
                  type="password"
                  autocomplete="current-password"
                  required
                  class="block w-full rounded-lg border @error('password') border-red-500 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-100"
                  placeholder=""
                />
              </div>
              @error('password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
              @enderror
            </div>

            <!-- Row: remember / forgot -->
            <div class="flex items-center justify-between">
              <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                <input
                  type="checkbox"
                  name="remember"
                  class="h-4 w-4 rounded border-gray-300 text-gray-900 focus:ring-gray-200"
                />
                Remember me
              </label>

              <a
                href="#"
                class="text-sm font-medium text-gray-700 underline underline-offset-4 hover:text-gray-900"
              >
                Forgot password?
              </a>
            </div>

            <!-- Button -->
            <button
              type="submit"
              class="mt-2 w-full rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 focus:outline-none focus:ring-4 focus:ring-gray-200 cursor-pointer"
            >
              Login
            </button>
          </form>
        </div>
        {{-- Card ends --}}
      </div>
    </main>
  </body>
</html>
