@extends('layouts.main')

@section('title', $currentDate->format('F Y') . ' • Calendar • Digital Bookings')

@section('content')
  <x-ls.page>
    <x-ls.page-header :title="$currentDate->format('F Y')">
      <x-slot name="actions">
        {{-- Platform Filter --}}
        <select x-data @change="window.location.href = '{{ route('calendar.index', ['year' => $currentDate->year, 'month' => $currentDate->month]) }}' + ($event.target.value ? '&platform_id=' + $event.target.value : '')"
          class="ls-select h-10 w-auto">
          <option value="">All Platforms</option>
          @foreach($platforms as $platform)
            <option value="{{ $platform->id }}" {{ (int) $platformId === $platform->id ? 'selected' : '' }}>
              {{ $platform->name }}
            </option>
          @endforeach
        </select>

        {{-- Placement Filter --}}
        <select x-data @change="window.location.href = '{{ route('calendar.index', ['year' => $currentDate->year, 'month' => $currentDate->month, 'platform_id' => $platformId]) }}' + ($event.target.value ? '&placement_id=' + $event.target.value : '')"
          class="ls-select h-10 w-auto">
          <option value="">All Placements</option>
          @foreach($placements as $placement)
            <option value="{{ $placement->id }}" {{ (int) $placementId === $placement->id ? 'selected' : '' }}>
              {{ $placement->name }}
            </option>
          @endforeach
        </select>

        {{-- Navigation --}}
        <div class="flex items-center rounded-lg border border-ls-border-strong bg-ls-surface">
          <a href="{{ route('calendar.index', ['year' => $prevMonth->year, 'month' => $prevMonth->month, 'platform_id' => $platformId, 'placement_id' => $placementId]) }}"
            class="flex h-10 w-10 items-center justify-center rounded-l-lg border-r border-ls-border-strong text-ls-text-muted hover:bg-ls-surface-muted hover:text-ls-text">
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd" d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" />
            </svg>
          </a>
          <a href="{{ route('calendar.index', ['year' => now()->year, 'month' => now()->month, 'platform_id' => $platformId, 'placement_id' => $placementId]) }}"
            class="flex h-10 items-center justify-center px-4 text-sm font-medium text-ls-text hover:bg-ls-surface-muted">
            Today
          </a>
          <a href="{{ route('calendar.index', ['year' => $nextMonth->year, 'month' => $nextMonth->month, 'platform_id' => $platformId, 'placement_id' => $placementId]) }}"
            class="flex h-10 w-10 items-center justify-center rounded-r-lg border-l border-ls-border-strong text-ls-text-muted hover:bg-ls-surface-muted hover:text-ls-text">
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
            </svg>
          </a>
        </div>

        {{-- Create reservation button --}}
        <x-ls.button :href="route('reservations.create')" variant="primary">
          Create reservation
        </x-ls.button>
      </x-slot>
    </x-ls.page-header>

    <x-ls.flash />

    @php
      // Skeleton sections used by the modal when a clicked day has no bookings.
      $emptyModalSections = collect(['lexpress.mu', '5plus.mu'])
        ->map(fn (string $name) => [
          'name' => $name,
          'groups' => [
            ['type' => 'Web', 'reservations' => []],
            ['type' => 'Social Media', 'reservations' => []],
          ],
        ])
        ->all();
    @endphp

    {{-- Calendar Grid (with day-detail modal) --}}
    <div
      x-data="{
        dayModalOpen: false,
        selectedDateLabel: '',
        selectedDatePlatforms: [],
        emptyDay: @js($emptyModalSections),
        bookingsByDate: @js($bookingsByDate),
        openDay(key, label) {
          this.selectedDateLabel = label;
          this.selectedDatePlatforms = this.bookingsByDate[key] ?? this.emptyDay;
          this.dayModalOpen = true;
        },
        totalReservations(platforms) {
          let total = 0;
          for (const p of platforms) {
            for (const g of p.groups) {
              total += g.reservations.length;
            }
          }
          return total;
        },
      }"
    >
      <div class="mt-6 overflow-hidden rounded-lg border border-ls-border-strong">
        {{-- Header --}}
        <div class="grid grid-cols-7 border-b border-ls-border-strong bg-ls-surface-muted">
          @foreach(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $dayName)
            <div class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider text-ls-text-muted">
              {{ $dayName }}
            </div>
          @endforeach
        </div>

        {{-- Weeks --}}
        <div class="divide-y divide-ls-border">
          @foreach($weeks as $week)
            <div class="grid grid-cols-7 divide-x divide-ls-border">
              @foreach($week as $day)
                @php
                  $dayKey = $day['date']->format('Y-m-d');
                  $dayLabel = $day['date']->format('l, j F Y');
                @endphp
                <div
                  role="button"
                  tabindex="0"
                  aria-label="View bookings for {{ $dayLabel }}"
                  @click="openDay('{{ $dayKey }}', '{{ $dayLabel }}')"
                  @keydown.enter.prevent="openDay('{{ $dayKey }}', '{{ $dayLabel }}')"
                  @keydown.space.prevent="openDay('{{ $dayKey }}', '{{ $dayLabel }}')"
                  class="min-h-[120px] cursor-pointer {{ $day['isCurrentMonth'] ? 'bg-ls-surface hover:bg-ls-surface-muted' : 'bg-ls-surface-muted hover:bg-ls-cream-dark' }} p-2 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-ls-deep"
                >
                  {{-- Day number --}}
                  <div class="flex items-start justify-between">
                    <span class="inline-flex h-7 w-7 items-center justify-center rounded-full text-sm {{ $day['isToday'] ? 'bg-ls-deep font-medium text-white' : ($day['isCurrentMonth'] ? 'text-ls-text' : 'text-ls-text-muted') }}">
                      {{ $day['date']->day }}
                    </span>
                  </div>

                  {{-- Reservations --}}
                  <div class="mt-1 space-y-1">
                    @foreach(array_slice($day['reservations'], 0, 3) as $reservation)
                      <a href="{{ route('reservations.show', $reservation) }}"
                        @click.stop
                        class="group block truncate rounded px-1.5 py-0.5 text-xs font-medium {{ $day['isCurrentMonth'] ? $reservation->status->calendarClasses() : 'bg-ls-surface-muted text-ls-text-muted' }}">
                        <span class="truncate">{{ $reservation->product }}</span>
                      </a>
                    @endforeach
                    @if(count($day['reservations']) > 3)
                      <span class="block px-1.5 text-xs text-ls-text-muted">
                        +{{ count($day['reservations']) - 3 }} more
                      </span>
                    @endif
                  </div>
                </div>
              @endforeach
            </div>
          @endforeach
        </div>
      </div>

      {{-- Day Detail Modal --}}
      <div x-show="dayModalOpen" x-cloak @keydown.escape.window="dayModalOpen = false" class="fixed inset-0 z-50 flex items-start justify-center px-4 pt-24" style="display: none;">
        <div class="fixed inset-0" style="background: var(--ls-overlay-scrim);" @click="dayModalOpen = false"></div>

        <div x-show="dayModalOpen" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="relative w-full max-w-2xl rounded-2xl bg-ls-surface shadow-xl ring-1 ring-ls-border-strong">
          <div class="flex items-start justify-between border-b border-ls-border px-6 py-4">
            <div>
              <h2 class="text-base font-medium text-ls-text" x-text="selectedDateLabel"></h2>
              <p class="mt-1 text-xs text-ls-text-muted">
                <span x-text="totalReservations(selectedDatePlatforms)"></span>
                <span x-text="totalReservations(selectedDatePlatforms) === 1 ? 'reservation' : 'reservations'"></span>
              </p>
            </div>
            <button type="button" @click="dayModalOpen = false" class="rounded-lg p-1 text-ls-text-muted hover:bg-ls-surface-muted hover:text-ls-text">
              <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
              </svg>
            </button>
          </div>

          <div class="max-h-[60vh] space-y-6 overflow-y-auto px-6 py-5">
            <template x-for="platform in selectedDatePlatforms" :key="platform.name">
              <section>
                <h3 class="text-sm font-medium uppercase tracking-wider text-ls-text" x-text="platform.name"></h3>

                <div class="mt-3 space-y-4">
                  <template x-for="group in platform.groups" :key="group.type">
                    <div>
                      <h4 class="text-xs font-medium uppercase tracking-wider text-ls-text-muted" x-text="group.type"></h4>

                      <ul class="mt-2 space-y-2">
                        <template x-for="reservation in group.reservations" :key="reservation.id">
                          <li>
                            <a :href="reservation.url" class="block rounded-lg border border-ls-border-strong px-3 py-2 hover:bg-ls-surface-muted">
                              <div class="flex items-center justify-between gap-3">
                                <span class="font-mono text-xs text-ls-text-muted" x-text="reservation.id"></span>
                                <span class="inline-flex items-center gap-1.5 text-xs text-ls-text">
                                  <span class="inline-block h-2 w-2 rounded-full" :class="reservation.status_dot_class"></span>
                                  <span x-text="reservation.status_label"></span>
                                </span>
                              </div>
                              <p class="mt-1 text-sm font-medium text-ls-text" x-text="reservation.product"></p>
                              <p class="text-xs text-ls-text-muted">
                                <span x-text="reservation.client"></span>
                                <span> &middot; </span>
                                <span x-text="reservation.placement"></span>
                              </p>
                            </a>
                          </li>
                        </template>
                        <template x-if="group.reservations.length === 0">
                          <li class="rounded-lg border border-dashed border-ls-border-strong px-3 py-2 text-xs text-ls-text-muted">
                            No bookings
                          </li>
                        </template>
                      </ul>
                    </div>
                  </template>
                </div>
              </section>
            </template>
          </div>
        </div>
      </div>
    </div>
  </x-ls.page>
@endsection
