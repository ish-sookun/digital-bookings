@extends('layouts.main')

@section('content')
  <main class="flex-1 bg-white">
    <div class="px-12 py-10">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-900">{{ $currentDate->format('F Y') }}</h1>

        <div class="flex items-center gap-4">
          {{-- Placement Filter --}}
          <div>
            <select x-data @change="window.location.href = '{{ route('calendar.index', ['year' => $currentDate->year, 'month' => $currentDate->month]) }}' + ($event.target.value ? '&placement_id=' + $event.target.value : '')"
              class="block w-full h-10 rounded-lg border-0 py-0 pl-3 pr-10 text-gray-900 ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-gray-900 sm:text-sm sm:leading-6">
              <option value="">All Placements</option>
              @foreach($placements as $placement)
                <option value="{{ $placement->id }}" {{ (int) $placementId === $placement->id ? 'selected' : '' }}>
                  {{ $placement->name }}
                </option>
              @endforeach
            </select>
          </div>

          {{-- Navigation --}}
          <div class="flex items-center rounded-lg border border-gray-200 bg-white">
            <a href="{{ route('calendar.index', ['year' => $prevMonth->year, 'month' => $prevMonth->month, 'placement_id' => $placementId]) }}"
              class="flex h-10 w-10 items-center justify-center rounded-l-lg border-r border-gray-200 text-gray-500 hover:bg-gray-50 hover:text-gray-700">
              <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" />
              </svg>
            </a>
            <a href="{{ route('calendar.index', ['year' => now()->year, 'month' => now()->month, 'placement_id' => $placementId]) }}"
              class="flex h-10 items-center justify-center px-4 text-sm font-medium text-gray-700 hover:bg-gray-50">
              Today
            </a>
            <a href="{{ route('calendar.index', ['year' => $nextMonth->year, 'month' => $nextMonth->month, 'placement_id' => $placementId]) }}"
              class="flex h-10 w-10 items-center justify-center rounded-r-lg border-l border-gray-200 text-gray-500 hover:bg-gray-50 hover:text-gray-700">
              <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
              </svg>
            </a>
          </div>

          {{-- Add booking button --}}
          <a href="{{ route('reservations.create') }}"
            class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 focus:outline-none focus:ring-4 focus:ring-gray-200">
            Add booking
          </a>
        </div>
      </div>

      <div class="mt-6 h-px w-full bg-gray-100"></div>

      {{-- Calendar Grid --}}
      <div class="mt-6 overflow-hidden rounded-lg border border-gray-200">
        {{-- Header --}}
        <div class="grid grid-cols-7 border-b border-gray-200 bg-gray-50">
          @foreach(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $dayName)
            <div class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-gray-500">
              {{ $dayName }}
            </div>
          @endforeach
        </div>

        {{-- Weeks --}}
        <div class="divide-y divide-gray-200">
          @foreach($weeks as $week)
            <div class="grid grid-cols-7 divide-x divide-gray-200">
              @foreach($week as $day)
                <div class="min-h-[120px] {{ $day['isCurrentMonth'] ? 'bg-white' : 'bg-gray-50' }} p-2">
                  {{-- Day number --}}
                  <div class="flex items-start justify-between">
                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-full text-sm {{ $day['isToday'] ? 'bg-gray-900 font-semibold text-white' : ($day['isCurrentMonth'] ? 'text-gray-900' : 'text-gray-400') }}">
                      {{ $day['date']->day }}
                    </span>
                  </div>

                  {{-- Bookings --}}
                  <div class="mt-1 space-y-1">
                    @foreach(array_slice($day['bookings'], 0, 3) as $booking)
                      <a href="{{ route('reservations.show', $booking) }}"
                        class="group block truncate rounded px-1.5 py-0.5 text-xs font-medium {{ $day['isCurrentMonth'] ? 'bg-gray-100 text-gray-700 hover:bg-gray-200' : 'bg-gray-100 text-gray-500' }}">
                        <span class="truncate">{{ $booking->product }}</span>
                      </a>
                    @endforeach
                    @if(count($day['bookings']) > 3)
                      <span class="block px-1.5 text-xs text-gray-500">
                        +{{ count($day['bookings']) - 3 }} more
                      </span>
                    @endif
                  </div>
                </div>
              @endforeach
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </main>
@endsection
