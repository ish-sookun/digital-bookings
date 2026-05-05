@extends('layouts.main')

@section('title', $reservation->product . ' • Digital Bookings')

@section('content')
  <x-ls.page>
    <x-ls.page-header :title="$reservation->product" :back="route('reservations.index')" />

    <div class="mt-8 max-w-2xl space-y-8">
      {{-- Reference & Status --}}
      <div class="grid grid-cols-2 gap-6">
        <div>
          <p class="text-sm font-medium text-ls-text">Reference</p>
          <p class="mt-1">
            <button
              type="button"
              x-data="copyToClipboard(@js($reservation->reference))"
              @click="copy()"
              :title="copied ? 'Copied!' : 'Click to copy'"
              class="inline-flex items-center gap-1.5 rounded-md px-2 py-1 font-mono text-xs font-medium ring-1 ring-inset cursor-pointer hover:brightness-95 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-ls-border-strong {{ $reservation->status->referenceClasses() }}"
            >
              {{ $reservation->reference }}
              <svg x-show="!copied" class="h-3 w-3 opacity-60" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184" />
              </svg>
              <svg x-show="copied" x-cloak class="h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="display: none;" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
              </svg>
            </button>
          </p>
        </div>
        <div>
          <p class="text-sm font-medium text-ls-text">Status</p>
          <p class="mt-1 inline-flex items-center gap-2 text-sm text-ls-text">
            <span class="inline-block h-2.5 w-2.5 rounded-full {{ $reservation->status->dotClasses() }}"></span>
            {{ $reservation->status->label() }}
          </p>
        </div>
      </div>

      {{-- Badges --}}
      <div class="flex flex-wrap gap-2">
        <x-ls.pill variant="neutral">{{ $reservation->type->label() }}</x-ls.pill>
        @if($reservation->is_cash)
          <x-ls.pill variant="success">Cash</x-ls.pill>
        @endif
        @if($reservation->bill_at_end_of_campaign)
          <x-ls.pill variant="warning">Bill at end of campaign</x-ls.pill>
        @endif
        @if($reservation->is_foreign_currency && $reservation->foreign_currency_code)
          <x-ls.pill variant="info">{{ $reservation->foreign_currency_code->value }} {{ number_format((float) $reservation->foreign_currency_amount, 2) }}</x-ls.pill>
        @endif
      </div>

      {{-- Client --}}
      <x-ls.section title="Client">
        <div class="grid grid-cols-2 gap-6">
          <div>
            <p class="text-sm font-medium text-ls-text">Client</p>
            <p class="mt-1 text-sm text-ls-text">{{ $reservation->client->company_name }}</p>
          </div>
          @if($reservation->representedClient)
            <div>
              <p class="text-sm font-medium text-ls-text">Representing</p>
              <p class="mt-1 text-sm text-ls-text">{{ $reservation->representedClient->company_name }}</p>
            </div>
          @endif
        </div>

        <div>
          <p class="text-sm font-medium text-ls-text">Salesperson</p>
          <p class="mt-1 text-sm text-ls-text">
            @if($reservation->salesperson)
              {{ $reservation->salesperson->first_name }} {{ $reservation->salesperson->last_name }}
            @else
              —
            @endif
          </p>
        </div>
      </x-ls.section>

      {{-- Product Details --}}
      <x-ls.section title="Product Details">
        <div>
          <p class="text-sm font-medium text-ls-text">Product</p>
          <p class="mt-1 text-sm text-ls-text">{{ $reservation->product }}</p>
        </div>

        <div class="grid grid-cols-2 gap-6">
          <div>
            <p class="text-sm font-medium text-ls-text">Platform</p>
            <p class="mt-1 text-sm text-ls-text">{{ $reservation->platform?->name ?? '—' }}</p>
          </div>
          <div>
            <p class="text-sm font-medium text-ls-text">Placement</p>
            <p class="mt-1 text-sm text-ls-text">{{ $reservation->placement->name }}</p>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-6">
          <div>
            <p class="text-sm font-medium text-ls-text">Channel</p>
            <p class="mt-1 text-sm text-ls-text">{{ $reservation->channel }}</p>
          </div>
          <div>
            <p class="text-sm font-medium text-ls-text">Scope</p>
            <p class="mt-1 text-sm text-ls-text">{{ $reservation->scope }}</p>
          </div>
        </div>
      </x-ls.section>

      {{-- Dates --}}
      <x-ls.section title="Reservation Dates">
        <div>
          <p class="text-sm font-medium text-ls-text">Dates Published</p>
          <div class="mt-2 flex flex-wrap gap-2">
            @foreach($reservation->dates_booked as $date)
              <x-ls.pill variant="neutral">{{ \Carbon\Carbon::parse($date)->format('d M Y') }}</x-ls.pill>
            @endforeach
          </div>
        </div>
      </x-ls.section>

      {{-- Financials --}}
      <x-ls.section title="Financials">
        <div class="grid grid-cols-2 gap-6">
          <div>
            <p class="text-sm font-medium text-ls-text">Gross Amount</p>
            <p class="mt-1 text-sm text-ls-text">MUR {{ number_format($reservation->gross_amount, 2) }}</p>
          </div>
          <div>
            <p class="text-sm font-medium text-ls-text">Discount</p>
            <p class="mt-1 text-sm text-ls-text">MUR {{ number_format($reservation->discount, 2) }}</p>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-6">
          <div>
            <p class="text-sm font-medium text-ls-text">Commission</p>
            <p class="mt-1 text-sm text-ls-text">MUR {{ number_format($reservation->commission, 2) }}</p>
          </div>
          <div>
            <p class="text-sm font-medium text-ls-text">VAT</p>
            <p class="mt-1 text-sm text-ls-text">MUR {{ number_format($reservation->vat, 2) }}</p>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-6">
          <div>
            <p class="text-sm font-medium text-ls-text">VAT Exempt</p>
            <p class="mt-1 text-sm text-ls-text">{{ $reservation->vat_exempt ? 'Yes' : 'No' }}</p>
          </div>
          <div>
            <p class="text-sm font-medium text-ls-text">Total Amount to Pay</p>
            <p class="mt-1 text-sm text-ls-text">MUR {{ number_format($reservation->total_amount_to_pay, 2) }}</p>
          </div>
        </div>

        @if($reservation->is_foreign_currency && $reservation->foreign_currency_code)
          <div>
            <p class="text-sm font-medium text-ls-text">Foreign Currency</p>
            <p class="mt-1 text-sm text-ls-text">
              {{ $reservation->foreign_currency_code->value }} {{ number_format((float) $reservation->foreign_currency_amount, 2) }}
            </p>
          </div>
        @endif
      </x-ls.section>

      {{-- Parent Reservation --}}
      @if($reservation->parent)
        <x-ls.section title="Parent Reservation">
          <div>
            <p class="text-sm font-medium text-ls-text">Linked to</p>
            <p class="mt-1 text-sm text-ls-text">
              <a href="{{ route('reservations.show', $reservation->parent) }}" class="inline-flex items-center gap-1.5 font-medium text-ls-deep underline hover:text-ls-text">
                <span class="font-mono text-xs">{{ $reservation->parent->reference }}</span>
                <span>— {{ $reservation->parent->product }}</span>
              </a>
            </p>
          </div>
        </x-ls.section>
      @endif

      {{-- Documents --}}
      <x-ls.section title="Documents">
        <div>
          <p class="text-sm font-medium text-ls-text mb-2">Reservation Order</p>
          <div class="flex items-center gap-3">
            <x-ls.button :href="route('reservations.pdf', $reservation)" variant="outline">
              <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
              </svg>
              Download RO
            </x-ls.button>
            @if($reservation->signed_ro_path)
              <x-ls.button :href="route('reservations.document', [$reservation, 'signed-ro'])" variant="outline">
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                Download Signed RO
              </x-ls.button>
            @endif
          </div>
        </div>

        <div class="grid grid-cols-3 gap-6">
          <div>
            <p class="text-sm font-medium text-ls-text">Purchase Order No.</p>
            <p class="mt-1 text-sm text-ls-text">{{ $reservation->purchase_order_no ?? '—' }}</p>
            @if($reservation->purchase_order_path)
              <a href="{{ route('reservations.document', [$reservation, 'purchase-order']) }}" class="mt-1 inline-flex items-center gap-1.5 text-xs font-medium text-ls-text-muted hover:text-ls-text">
                <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                Download PO
              </a>
            @endif
          </div>
          <div>
            <p class="text-sm font-medium text-ls-text">Invoice No.</p>
            <p class="mt-1 text-sm text-ls-text">{{ $reservation->invoice_no ?? '—' }}</p>
            @if($reservation->invoice_path)
              <a href="{{ route('reservations.document', [$reservation, 'invoice']) }}" class="mt-1 inline-flex items-center gap-1.5 text-xs font-medium text-ls-text-muted hover:text-ls-text">
                <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                Download Invoice
              </a>
            @endif
          </div>
          <div>
            <p class="text-sm font-medium text-ls-text">Receipt No.</p>
            <p class="mt-1 text-sm text-ls-text">{{ $reservation->receipt_no ?? '—' }}</p>
            @if($reservation->receipt_path)
              <a href="{{ route('reservations.document', [$reservation, 'receipt']) }}" class="mt-1 inline-flex items-center gap-1.5 text-xs font-medium text-ls-text-muted hover:text-ls-text">
                <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                Download Receipt
              </a>
            @endif
          </div>
        </div>
      </x-ls.section>

      {{-- Remark --}}
      @if($reservation->remark)
        <x-ls.section title="Additional Information">
          <div>
            <p class="text-sm font-medium text-ls-text">Remark</p>
            <p class="mt-1 text-sm text-ls-text whitespace-pre-wrap">{{ $reservation->remark }}</p>
          </div>
        </x-ls.section>
      @endif

      <div class="flex items-center gap-4">
        <x-ls.button :href="route('reservations.edit', $reservation)" variant="primary">Edit Reservation</x-ls.button>
        <x-ls.button :href="route('reservations.index')" variant="ghost">Back to Reservations</x-ls.button>
      </div>
    </div>
  </x-ls.page>
@endsection
