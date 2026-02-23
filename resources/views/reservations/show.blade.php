@extends('layouts.main')

@section('content')
  <main class="flex-1 bg-white">
    <div class="px-12 py-10">
      <div class="flex items-center gap-4">
        <a href="{{ route('reservations.index') }}" class="text-gray-400 hover:text-gray-600">
          <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M17 10a.75.75 0 0 1-.75.75H5.612l4.158 3.96a.75.75 0 1 1-1.04 1.08l-5.5-5.25a.75.75 0 0 1 0-1.08l5.5-5.25a.75.75 0 0 1 1.04 1.08L5.612 9.25H16.25A.75.75 0 0 1 17 10Z" clip-rule="evenodd" />
          </svg>
        </a>
        <h1 class="text-2xl font-semibold text-gray-900">{{ $reservation->product }}</h1>
      </div>
      <div class="mt-6 h-px w-full bg-gray-100"></div>

      <div class="mt-8 max-w-2xl space-y-8">
        {{-- Client & Agency --}}
        <div class="space-y-6">
          <h2 class="text-lg font-medium text-gray-900">Client & Agency</h2>

          <div class="grid grid-cols-2 gap-6">
            <div>
              <p class="text-sm font-medium text-gray-700">Client</p>
              <p class="mt-1 text-sm text-gray-900">{{ $reservation->client->company_name }}</p>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-700">Agency</p>
              <p class="mt-1 text-sm text-gray-900">{{ $reservation->agency?->company_name ?? '—' }}</p>
            </div>
          </div>

          <div>
            <p class="text-sm font-medium text-gray-700">Salesperson</p>
            <p class="mt-1 text-sm text-gray-900">
              @if($reservation->salesperson)
                {{ $reservation->salesperson->first_name }} {{ $reservation->salesperson->last_name }}
              @else
                —
              @endif
            </p>
          </div>
        </div>

        {{-- Product Details --}}
        <div class="space-y-6">
          <h2 class="text-lg font-medium text-gray-900">Product Details</h2>

          <div>
            <p class="text-sm font-medium text-gray-700">Product</p>
            <p class="mt-1 text-sm text-gray-900">{{ $reservation->product }}</p>
          </div>

          <div class="grid grid-cols-2 gap-6">
            <div>
              <p class="text-sm font-medium text-gray-700">Platform</p>
              <p class="mt-1 text-sm text-gray-900">{{ $reservation->platform?->name ?? '—' }}</p>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-700">Placement</p>
              <p class="mt-1 text-sm text-gray-900">{{ $reservation->placement->name }}</p>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-6">
            <div>
              <p class="text-sm font-medium text-gray-700">Channel</p>
              <p class="mt-1 text-sm text-gray-900">{{ $reservation->channel }}</p>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-700">Scope</p>
              <p class="mt-1 text-sm text-gray-900">{{ $reservation->scope }}</p>
            </div>
          </div>
        </div>

        {{-- Dates --}}
        <div class="space-y-6">
          <h2 class="text-lg font-medium text-gray-900">Booking Dates</h2>

          <div>
            <p class="text-sm font-medium text-gray-700">Dates Booked</p>
            <div class="mt-2 flex flex-wrap gap-2">
              @foreach($reservation->dates_booked as $date)
                <span class="inline-flex items-center rounded-md bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700">
                  {{ \Carbon\Carbon::parse($date)->format('d M Y') }}
                </span>
              @endforeach
            </div>
          </div>
        </div>

        {{-- Financials --}}
        <div class="space-y-6">
          <h2 class="text-lg font-medium text-gray-900">Financials</h2>

          <div class="grid grid-cols-2 gap-6">
            <div>
              <p class="text-sm font-medium text-gray-700">Gross Amount</p>
              <p class="mt-1 text-sm text-gray-900">MUR {{ number_format($reservation->gross_amount, 2) }}</p>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-700">Discount</p>
              <p class="mt-1 text-sm text-gray-900">MUR {{ number_format($reservation->discount, 2) }}</p>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-6">
            <div>
              <p class="text-sm font-medium text-gray-700">Commission</p>
              <p class="mt-1 text-sm text-gray-900">MUR {{ number_format($reservation->commission, 2) }}</p>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-700">Cost of Artwork</p>
              <p class="mt-1 text-sm text-gray-900">MUR {{ number_format($reservation->cost_of_artwork, 2) }}</p>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-6">
            <div>
              <p class="text-sm font-medium text-gray-700">VAT</p>
              <p class="mt-1 text-sm text-gray-900">MUR {{ number_format($reservation->vat, 2) }}</p>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-700">VAT Exempt</p>
              <p class="mt-1 text-sm text-gray-900">{{ $reservation->vat_exempt ? 'Yes' : 'No' }}</p>
            </div>
          </div>

          <div>
            <p class="text-sm font-medium text-gray-700">Total Amount to Pay</p>
            <p class="mt-1 text-sm text-gray-900">MUR {{ number_format($reservation->total_amount_to_pay, 2) }}</p>
          </div>
        </div>

        {{-- Reference Numbers --}}
        <div class="space-y-6">
          <h2 class="text-lg font-medium text-gray-900">Reference Numbers</h2>

          <div class="grid grid-cols-2 gap-6">
            <div>
              <p class="text-sm font-medium text-gray-700">Purchase Order No.</p>
              <p class="mt-1 text-sm text-gray-900">{{ $reservation->purchase_order_no ?? '—' }}</p>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-700">Invoice No.</p>
              <p class="mt-1 text-sm text-gray-900">{{ $reservation->invoice_no ?? '—' }}</p>
            </div>
          </div>
        </div>

        {{-- Remark --}}
        @if($reservation->remark)
          <div class="space-y-6">
            <h2 class="text-lg font-medium text-gray-900">Additional Information</h2>

            <div>
              <p class="text-sm font-medium text-gray-700">Remark</p>
              <p class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{{ $reservation->remark }}</p>
            </div>
          </div>
        @endif

        <div class="flex items-center gap-4">
          <a href="{{ route('reservations.edit', $reservation) }}" class="rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 focus:outline-none focus:ring-4 focus:ring-gray-200">
            Edit Booking
          </a>
          <a href="{{ route('reservations.index') }}" class="text-sm font-medium text-gray-700 hover:text-gray-900">
            Back to Bookings
          </a>
        </div>
      </div>
    </div>
  </main>
@endsection
