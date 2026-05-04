@extends('layouts.main')

@section('title', 'Client • Digital Bookings')

@section('content')
  <x-ls.page>
    <x-ls.page-header :title="$client->company_name" :back="route('clients.index')" />

    <div class="mt-8 grid grid-cols-1 gap-12 lg:grid-cols-2">
      <div class="space-y-8">
        {{-- Company Details --}}
        <x-ls.section title="Company Details">
          @if($client->company_logo)
            <div>
              <p class="text-sm font-medium text-ls-text">Logo</p>
              <div class="mt-2">
                <img src="{{ Storage::url($client->company_logo) }}" alt="{{ $client->company_name }}" class="max-h-24 rounded-md object-contain" />
              </div>
            </div>
          @endif

          <div class="grid grid-cols-2 gap-6">
            <div>
              <p class="text-sm font-medium text-ls-text">Company Name</p>
              <p class="mt-1 text-sm text-ls-text-muted">{{ $client->company_name }}</p>
            </div>
            <div>
              <p class="text-sm font-medium text-ls-text">BRN</p>
              <p class="mt-1 text-sm text-ls-text-muted">{{ $client->brn }}</p>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-6">
            <div>
              <p class="text-sm font-medium text-ls-text">Phone</p>
              <p class="mt-1 text-sm text-ls-text-muted">{{ $client->phone }}</p>
            </div>
            <div>
              <p class="text-sm font-medium text-ls-text">Address</p>
              <p class="mt-1 text-sm text-ls-text-muted">{{ $client->address }}</p>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-6">
            <div>
              <p class="text-sm font-medium text-ls-text">VAT Number</p>
              <p class="mt-1 text-sm text-ls-text-muted">{{ $client->vat_number ?? '—' }}</p>
            </div>
            <div>
              <p class="text-sm font-medium text-ls-text">VAT Exempt</p>
              <p class="mt-1 text-sm text-ls-text-muted">{{ $client->vat_exempt ? 'Yes' : 'No' }}</p>
            </div>
          </div>
        </x-ls.section>

        {{-- Commission --}}
        <x-ls.section title="Commission">
          <div class="grid grid-cols-2 gap-6">
            <div>
              <p class="text-sm font-medium text-ls-text">Amount</p>
              <p class="mt-1 text-sm text-ls-text-muted">{{ $client->commission_amount ?? '—' }}</p>
            </div>
            <div>
              <p class="text-sm font-medium text-ls-text">Type</p>
              <p class="mt-1 text-sm text-ls-text-muted">{{ $client->commission_type?->value ?? '—' }}</p>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-6">
            <div>
              <p class="text-sm font-medium text-ls-text">Discount Amount</p>
              <p class="mt-1 text-sm text-ls-text-muted">{{ $client->discount ?? '—' }}</p>
            </div>
            <div>
              <p class="text-sm font-medium text-ls-text">Discount Type</p>
              <p class="mt-1 text-sm text-ls-text-muted">{{ $client->discount_type?->value ?? '—' }}</p>
            </div>
          </div>
        </x-ls.section>

        {{-- Contact Person --}}
        <x-ls.section title="Contact Person">
          <div>
            <p class="text-sm font-medium text-ls-text">Name</p>
            <p class="mt-1 text-sm text-ls-text-muted">{{ $client->contact_person_name ?? '—' }}</p>
          </div>

          <div class="grid grid-cols-2 gap-6">
            <div>
              <p class="text-sm font-medium text-ls-text">Email</p>
              <p class="mt-1 text-sm text-ls-text-muted">{{ $client->contact_person_email ?? '—' }}</p>
            </div>
            <div>
              <p class="text-sm font-medium text-ls-text">Phone</p>
              <p class="mt-1 text-sm text-ls-text-muted">{{ $client->contact_person_phone ?? '—' }}</p>
            </div>
          </div>
        </x-ls.section>

        <div class="flex items-center gap-4">
          <x-ls.button :href="route('clients.edit', $client)" variant="primary">Edit Client</x-ls.button>
          <x-ls.button :href="route('clients.index')" variant="ghost">Back to Clients</x-ls.button>
        </div>
      </div>

      {{-- Reservations --}}
      <div>
        <h2 class="text-lg font-medium text-ls-text">Reservations</h2>
        <p class="mt-1 text-xs text-ls-text-soft">{{ $reservations->total() }} total</p>

        <div class="mt-4 space-y-3">
          @forelse($reservations as $reservation)
            <a href="{{ route('reservations.show', $reservation) }}" class="block rounded-lg border border-ls-border p-4 hover:bg-ls-surface-muted">
              <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-ls-text">{{ $reservation->product }}</p>
                @php
                  $statusVariant = match($reservation->status) {
                    \App\ReservationStatus::Confirmed => 'success',
                    \App\ReservationStatus::Canceled => 'danger',
                    default => 'warning',
                  };
                @endphp
                <x-ls.pill :variant="$statusVariant">{{ $reservation->status->value }}</x-ls.pill>
              </div>
              <div class="mt-2 flex items-center gap-4 text-xs text-ls-text-muted">
                <span>{{ $reservation->platform->name }}</span>
                <span>{{ $reservation->placement->name }}</span>
                <span>{{ $reservation->salesperson->first_name }} {{ $reservation->salesperson->last_name }}</span>
              </div>
              <div class="mt-1 flex items-center justify-between text-xs">
                <span class="text-ls-text-soft">{{ $reservation->created_at->format('d M Y') }}</span>
                <span class="font-medium text-ls-text">MUR {{ number_format($reservation->gross_amount) }}</span>
              </div>
            </a>
          @empty
            <p class="text-sm text-ls-text-muted">No reservations found.</p>
          @endforelse
        </div>

        <div class="mt-4">
          {{ $reservations->links() }}
        </div>
      </div>
    </div>
  </x-ls.page>
@endsection
