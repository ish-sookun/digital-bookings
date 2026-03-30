<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <title>Login • Digital Bookings</title>

    {{-- Styles / Scripts --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
  </head>

  <body class="bg-gray-100">
    <div class="min-h-screen p-2">
      <!-- App shell -->
      <div class="min-h-[calc(100vh-1rem)] overflow-hidden rounded-2xl bg-white shadow-sm ring-1 ring-gray-200">
        <div class="flex min-h-[calc(100vh-1rem)]">
          <!-- Sidebar -->
          <aside class="w-72 bg-gray-50 border-r border-gray-200 flex flex-col">
            <!-- Workspace header -->
            <div class="px-4 py-4">
              <div class="flex items-center gap-3 px-1 py-2">
                <span class="inline-flex h-8 w-8 items-center justify-center text-gray-900">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 80 80" fill="none" role="img" aria-label="Digital Bookings Icon">
                    <g stroke="currentColor" stroke-width="5" stroke-linecap="round" stroke-linejoin="round">
                      <rect x="14" y="18" width="52" height="34" rx="8" />
                      <path d="M30 62h20" />
                      <path d="M40 52v10" />
                      <path d="M26 36l8 8 18-18" />
                    </g>
                  </svg>
                </span>

                <div class="min-w-0 flex-1">
                  <p class="truncate text-sm font-semibold text-gray-900">Digital Bookings</p>
                  <p class="truncate text-xs text-gray-500">La Sentinelle</p>
                </div>
              </div>
            </div>

            <!-- Quick links -->
            <nav class="px-3 pb-3">
              <ul class="space-y-1">
                <li>
                  <a href="#" class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm text-gray-700 hover:bg-white/70">
                    <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                      <path
                        fill-rule="evenodd"
                        d="M8.5 3.5a5 5 0 1 0 2.98 9.02l3.25 3.25a.75.75 0 1 0 1.06-1.06l-3.25-3.25A5 5 0 0 0 8.5 3.5Zm-3.5 5a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0Z"
                        clip-rule="evenodd"
                      />
                    </svg>
                    Search
                  </a>
                </li>
              </ul>
            </nav>

            <div class="border-t border-gray-200"></div>

            <!-- Main nav -->
            <nav class="px-3 py-3">
              <ul class="space-y-1">
                <!-- Active -->
                <li>
                  <a
                    href="{{ route('home') }}"
                    class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm {{ request()->routeIs('home') ? 'font-semibold text-gray-900 bg-white shadow-sm ring-1 ring-gray-200' : 'text-gray-700 hover:bg-white/70' }}"
                  >
                    <svg class="h-5 w-5 {{ request()->routeIs('home') ? 'text-gray-900' : 'text-gray-400' }}" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                      <path d="M9.293 2.293a1 1 0 0 1 1.414 0l6 6A1 1 0 0 1 16 10h-1v6a2 2 0 0 1-2 2h-2a1 1 0 0 1-1-1v-4H10v4a1 1 0 0 1-1 1H7a2 2 0 0 1-2-2v-6H4a1 1 0 0 1-.707-1.707l6-6Z" />
                    </svg>
                    Home
                  </a>
                </li>

                {{-- Bookings --}}
                <li>
                  <a href="{{ route('reservations.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm {{ request()->routeIs('reservations.*') ? 'font-semibold text-gray-900 bg-white shadow-sm ring-1 ring-gray-200' : 'text-gray-700 hover:bg-white/70' }}">
                    <svg class="h-5 w-5 {{ request()->routeIs('reservations.*') ? 'text-gray-900' : 'text-gray-400' }}" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                      <path d="M6 3a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V7.414A2 2 0 0 0 15.414 6L13 3.586A2 2 0 0 0 11.586 3H6Z" />
                    </svg>
                    Bookings
                  </a>
                </li>

                {{-- Clients --}}
                <li>
                  <a href="{{ route('clients.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm {{ request()->routeIs('clients.*') ? 'font-semibold text-gray-900 bg-white shadow-sm ring-1 ring-gray-200' : 'text-gray-700 hover:bg-white/70' }}">
                    <svg class="h-5 w-5 {{ request()->routeIs('clients.*') ? 'text-gray-900' : 'text-gray-400' }}" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                      <path d="M7 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM14.5 9a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5ZM1.615 16.428a1.224 1.224 0 0 1-.569-1.175 6.002 6.002 0 0 1 11.908 0c.058.467-.172.92-.57 1.174A9.953 9.953 0 0 1 7 18a9.953 9.953 0 0 1-5.385-1.572ZM14.5 16h-.106c.07-.297.088-.611.048-.933a7.47 7.47 0 0 0-1.588-3.755 4.502 4.502 0 0 1 5.874 2.636.818.818 0 0 1-.36.98A7.465 7.465 0 0 1 14.5 16Z" />
                    </svg>
                    Clients
                  </a>
                </li>

                {{-- Agencies --}}
                <li>
                  <a href="{{ route('agencies.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm {{ request()->routeIs('agencies.*') ? 'font-semibold text-gray-900 bg-white shadow-sm ring-1 ring-gray-200' : 'text-gray-700 hover:bg-white/70' }}">
                    <svg class="h-5 w-5 {{ request()->routeIs('agencies.*') ? 'text-gray-900' : 'text-gray-400' }}" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                      <path fill-rule="evenodd" d="M4 16.5v-13h-.25a.75.75 0 0 1 0-1.5h12.5a.75.75 0 0 1 0 1.5H16v13h.25a.75.75 0 0 1 0 1.5H3.75a.75.75 0 0 1 0-1.5H4Zm3-11a.75.75 0 0 1 .75-.75h4.5a.75.75 0 0 1 0 1.5h-4.5A.75.75 0 0 1 7 5.5Zm.75 2.25a.75.75 0 0 0 0 1.5h4.5a.75.75 0 0 0 0-1.5h-4.5ZM8 12a2 2 0 0 1 4 0v4.5H8V12Z" clip-rule="evenodd" />
                    </svg>
                    Agencies
                  </a>
                </li>

                {{-- Placements --}}
                <li>
                  <a href="{{ route('placements.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm {{ request()->routeIs('placements.*') ? 'font-semibold text-gray-900 bg-white shadow-sm ring-1 ring-gray-200' : 'text-gray-700 hover:bg-white/70' }}">
                    <svg class="h-5 w-5 {{ request()->routeIs('placements.*') ? 'text-gray-900' : 'text-gray-400' }}" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                      <path fill-rule="evenodd" d="M4.25 2A2.25 2.25 0 0 0 2 4.25v2.5A2.25 2.25 0 0 0 4.25 9h2.5A2.25 2.25 0 0 0 9 6.75v-2.5A2.25 2.25 0 0 0 6.75 2h-2.5Zm0 9A2.25 2.25 0 0 0 2 13.25v2.5A2.25 2.25 0 0 0 4.25 18h2.5A2.25 2.25 0 0 0 9 15.75v-2.5A2.25 2.25 0 0 0 6.75 11h-2.5Zm9-9A2.25 2.25 0 0 0 11 4.25v2.5A2.25 2.25 0 0 0 13.25 9h2.5A2.25 2.25 0 0 0 18 6.75v-2.5A2.25 2.25 0 0 0 15.75 2h-2.5Zm0 9A2.25 2.25 0 0 0 11 13.25v2.5A2.25 2.25 0 0 0 13.25 18h2.5A2.25 2.25 0 0 0 18 15.75v-2.5A2.25 2.25 0 0 0 15.75 11h-2.5Z" clip-rule="evenodd" />
                    </svg>
                    Placements
                  </a>
                </li>

                {{-- Platforms --}}
                <li>
                  <a href="{{ route('platforms.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm {{ request()->routeIs('platforms.*') ? 'font-semibold text-gray-900 bg-white shadow-sm ring-1 ring-gray-200' : 'text-gray-700 hover:bg-white/70' }}">
                    <svg class="h-5 w-5 {{ request()->routeIs('platforms.*') ? 'text-gray-900' : 'text-gray-400' }}" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                      <path fill-rule="evenodd" d="M2 4.25A2.25 2.25 0 0 1 4.25 2h11.5A2.25 2.25 0 0 1 18 4.25v8.5A2.25 2.25 0 0 1 15.75 15h-3.105a3.501 3.501 0 0 0 1.1 1.677A.75.75 0 0 1 13.26 18H6.74a.75.75 0 0 1-.484-1.323A3.501 3.501 0 0 0 7.355 15H4.25A2.25 2.25 0 0 1 2 12.75v-8.5Zm1.5 0a.75.75 0 0 1 .75-.75h11.5a.75.75 0 0 1 .75.75v7.5a.75.75 0 0 1-.75.75H4.25a.75.75 0 0 1-.75-.75v-7.5Z" clip-rule="evenodd" />
                    </svg>
                    Platforms
                  </a>
                </li>

                {{-- Salespersons --}}
                <li>
                  <a href="{{ route('salespeople.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm {{ request()->routeIs('salespeople.*') ? 'font-semibold text-gray-900 bg-white shadow-sm ring-1 ring-gray-200' : 'text-gray-700 hover:bg-white/70' }}">
                    <svg class="h-5 w-5 {{ request()->routeIs('salespeople.*') ? 'text-gray-900' : 'text-gray-400' }}" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                      <path d="M10 2a4 4 0 1 0 0 8 4 4 0 0 0 0-8ZM3 16a7 7 0 0 1 14 0v1H3v-1Z" />
                    </svg>
                    Salespersons
                  </a>
                </li>

                {{-- Calendar --}}
                <li>
                  <a href="{{ route('calendar.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm {{ request()->routeIs('calendar.*') ? 'font-semibold text-gray-900 bg-white shadow-sm ring-1 ring-gray-200' : 'text-gray-700 hover:bg-white/70' }}">
                    <svg class="h-5 w-5 {{ request()->routeIs('calendar.*') ? 'text-gray-900' : 'text-gray-400' }}" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                      <path fill-rule="evenodd" d="M5.75 2a.75.75 0 0 1 .75.75V4h7V2.75a.75.75 0 0 1 1.5 0V4h.25A2.75 2.75 0 0 1 18 6.75v8.5A2.75 2.75 0 0 1 15.25 18H4.75A2.75 2.75 0 0 1 2 15.25v-8.5A2.75 2.75 0 0 1 4.75 4H5V2.75A.75.75 0 0 1 5.75 2Zm-1 5.5c-.69 0-1.25.56-1.25 1.25v6.5c0 .69.56 1.25 1.25 1.25h10.5c.69 0 1.25-.56 1.25-1.25v-6.5c0-.69-.56-1.25-1.25-1.25H4.75Z" clip-rule="evenodd" />
                    </svg>
                    Calendar
                  </a>
                </li>
              </ul>
            </nav>

            <!-- Today's Bookings -->
            <div class="px-4 pt-4">
              <h3 class="text-xs font-semibold text-gray-500">Today's Bookings</h3>
              @php
                $todaysBookings = \App\Models\Reservation::with('client')
                  ->get()
                  ->filter(function ($reservation) {
                    return in_array(now()->format('Y-m-d'), $reservation->dates_booked);
                  })
                  ->take(5);
              @endphp
              <ul class="mt-3 space-y-3 text-sm">
                @forelse($todaysBookings as $index => $booking)
                  <li class="{{ $index === 0 ? 'font-semibold text-gray-900' : 'text-gray-800' }}">
                    <a href="{{ route('reservations.show', $booking) }}" class="hover:underline">
                      {{ Str::limit($booking->product, 20) }} • {{ Str::limit($booking->client->company_name, 15) }}
                    </a>
                  </li>
                @empty
                  <li class="text-gray-500">No bookings today</li>
                @endforelse
              </ul>
            </div>

            <div class="flex-1"></div>

            <!-- Support / Changelog -->
            <div class="px-3 pb-3 hidden">
              <ul class="space-y-1">
                <li>
                  <a href="#" class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm text-gray-700 hover:bg-white/70">
                    <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                      <path
                        fill-rule="evenodd"
                        d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-11a.75.75 0 0 0-1.5 0v3.5c0 .414.336.75.75.75h2a.75.75 0 0 0 0-1.5h-1.25V7Z"
                        clip-rule="evenodd"
                      />
                    </svg>
                    Support
                  </a>
                </li>
                <li>
                  <a href="#" class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm text-gray-700 hover:bg-white/70">
                    <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                      <path d="M4 3a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5a2 2 0 0 0-2-2H10.5L9 3.5A2 2 0 0 0 7.5 3H4Z" />
                    </svg>
                    Changelog
                  </a>
                </li>
              </ul>
            </div>

            <div class="border-t border-gray-200"></div>

            <!-- Profile -->
            <div class="px-4 py-4" x-data="{ open: false }">
              <div class="relative">
                <!-- Dropdown menu -->
                <div
                  x-show="open"
                  @click.away="open = false"
                  x-transition:enter="transition ease-out duration-200"
                  x-transition:enter-start="opacity-0 translate-y-1"
                  x-transition:enter-end="opacity-100 translate-y-0"
                  x-transition:leave="transition ease-in duration-150"
                  x-transition:leave-start="opacity-100 translate-y-0"
                  x-transition:leave-end="opacity-0 translate-y-1"
                  class="absolute bottom-full left-0 right-0 mb-2 rounded-xl bg-white shadow-lg ring-1 ring-gray-200 py-1"
                  style="display: none;"
                >
                  <a href="{{ route('profile.show') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                    Edit Profile
                  </a>
                  <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 cursor-pointer">
                      Log out
                    </button>
                  </form>
                </div>

                <!-- Profile button -->
                <button type="button" @click="open = !open" class="w-full flex items-center gap-3 rounded-xl px-3 py-2 hover:bg-white/70 cursor-pointer">
                  <div class="min-w-0 flex-1 text-left">
                    <p class="truncate text-sm font-semibold text-gray-900">{{ auth()->user()->firstname }} {{ auth()->user()->lastname }}</p>
                    <p class="truncate text-xs text-gray-500">{{ auth()->user()->email }}</p>
                  </div>
                  <svg class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path
                      fill-rule="evenodd"
                      d="M14.77 12.79a.75.75 0 0 1-1.06-.02L10 9.06l-3.71 3.71a.75.75 0 0 1-1.06-1.06l4.24-4.25a.75.75 0 0 1 1.06 0l4.24 4.25a.75.75 0 0 1-.02 1.08Z"
                      clip-rule="evenodd"
                    />
                  </svg>
                </button>
              </div>
            </div>
          </aside>

          <!-- Main content -->
          @yield('content')
        </div>
      </div>
    </div>
  </body>
</html>
