@extends('layouts.main')

@section('title', 'Reservations • Digital Bookings')

@section('content')
  <x-ls.page>
    <x-ls.page-header title="Reservations">
      <x-slot name="actions">
        @can('sage-export')
          <form method="GET" action="{{ route('reservations.sage-export') }}" class="flex flex-wrap items-center gap-2" x-data="{ start: '', end: '' }">
            <input type="date" name="start_date" x-model="start" required class="ls-input" style="width: auto;">
            <input type="date" name="end_date" x-model="end" required class="ls-input" style="width: auto;">
            <select name="payment_mode" class="ls-select" style="width: auto;">
              <option value="credit">Credit</option>
              <option value="cash">Cash</option>
            </select>
            <button type="submit" class="ls-btn ls-btn-primary cursor-pointer">
              SAGE Export
            </button>
          </form>
        @endcan
        <x-ls.button :href="route('reservations.create')" variant="primary">Add Reservation</x-ls.button>
      </x-slot>
    </x-ls.page-header>

    <div class="mt-6">
      <x-ls.flash />
    </div>

    <div class="mt-6">
      <table class="ls-table">
        <thead>
          <tr>
            <th>Reference</th>
            <th>Status</th>
            <th>Client</th>
            <th>Product</th>
            <th>Platform</th>
            <th>Placement</th>
            <th>Total to Pay</th>
            <th class="text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($reservations as $reservation)
            <tr>
              <td>
                <button
                  type="button"
                  x-data="copyToClipboard(@js($reservation->id))"
                  @click="copy()"
                  :title="copied ? 'Copied!' : 'Click to copy'"
                  class="inline-flex items-center gap-1.5 rounded-md px-2 py-1 font-mono text-xs font-medium ring-1 ring-inset cursor-pointer hover:brightness-95 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-ls-border-strong {{ $reservation->status->referenceClasses() }}"
                >
                  {{ $reservation->id }}
                  <svg x-show="!copied" class="h-3 w-3 opacity-60" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" />
                  </svg>
                  <svg x-show="copied" x-cloak class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="display: none;" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                  </svg>
                </button>
              </td>
              <td>
                <span class="inline-flex items-center gap-2">
                  <span class="inline-block h-2.5 w-2.5 rounded-full {{ $reservation->status->dotClasses() }}"></span>
                  {{ $reservation->status->label() }}
                </span>
              </td>
              <td>{{ $reservation->client->company_name }}</td>
              <td>{{ $reservation->product }}</td>
              <td>{{ $reservation->platform?->name ?? '—' }}</td>
              <td>{{ $reservation->placement->name }}</td>
              <td>MUR {{ number_format($reservation->total_amount_to_pay, 2) }}</td>
              <td class="text-right">
                <div class="flex items-center justify-end gap-2">
                  <x-ls.button :href="route('reservations.show', $reservation)" variant="outline" size="sm">View</x-ls.button>
                  <x-ls.button :href="route('reservations.edit', $reservation)" variant="outline" size="sm">Edit</x-ls.button>
                  <form action="{{ route('reservations.destroy', $reservation) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this reservation?')">
                    @csrf
                    @method('DELETE')
                    <x-ls.button type="submit" variant="danger" size="sm">Delete</x-ls.button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8" class="text-center" style="padding: 40px 12px; color: var(--ls-text-muted);">
                No reservations found. Click "Add Reservation" to create one.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>

      @if($reservations->hasPages())
        <div class="mt-6">
          {{ $reservations->links() }}
        </div>
      @endif
    </div>
  </x-ls.page>
@endsection
