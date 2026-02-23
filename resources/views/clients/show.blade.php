@extends('layouts.main')

@section('content')
  <main class="flex-1 bg-white">
    <div class="px-12 py-10">
      <div class="flex items-center gap-4">
        <a href="{{ route('clients.index') }}" class="text-gray-400 hover:text-gray-600">
          <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M17 10a.75.75 0 0 1-.75.75H5.612l4.158 3.96a.75.75 0 1 1-1.04 1.08l-5.5-5.25a.75.75 0 0 1 0-1.08l5.5-5.25a.75.75 0 0 1 1.04 1.08L5.612 9.25H16.25A.75.75 0 0 1 17 10Z" clip-rule="evenodd" />
          </svg>
        </a>
        <h1 class="text-2xl font-semibold text-gray-900">{{ $client->company_name }}</h1>
      </div>
      <div class="mt-6 h-px w-full bg-gray-100"></div>

      <div class="mt-8 max-w-2xl space-y-8">
        {{-- Company Details --}}
        <div class="space-y-6">
          <h2 class="text-lg font-medium text-gray-900">Company Details</h2>

          @if($client->company_logo)
            <div>
              <p class="text-sm font-medium text-gray-700">Logo</p>
              <div class="mt-2">
                <img src="{{ Storage::url($client->company_logo) }}" alt="{{ $client->company_name }}" class="max-h-24 rounded-lg object-contain" />
              </div>
            </div>
          @endif

          <div class="grid grid-cols-2 gap-6">
            <div>
              <p class="text-sm font-medium text-gray-700">Company Name</p>
              <p class="mt-1 text-sm text-gray-900">{{ $client->company_name }}</p>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-700">BRN</p>
              <p class="mt-1 text-sm text-gray-900">{{ $client->brn }}</p>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-6">
            <div>
              <p class="text-sm font-medium text-gray-700">Phone</p>
              <p class="mt-1 text-sm text-gray-900">{{ $client->phone }}</p>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-700">Address</p>
              <p class="mt-1 text-sm text-gray-900">{{ $client->address }}</p>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-6">
            <div>
              <p class="text-sm font-medium text-gray-700">VAT Number</p>
              <p class="mt-1 text-sm text-gray-900">{{ $client->vat_number ?? '—' }}</p>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-700">VAT Exempt</p>
              <p class="mt-1 text-sm text-gray-900">{{ $client->vat_exempt ? 'Yes' : 'No' }}</p>
            </div>
          </div>
        </div>

        {{-- Commission --}}
        <div class="space-y-6">
          <h2 class="text-lg font-medium text-gray-900">Commission</h2>

          <div class="grid grid-cols-2 gap-6">
            <div>
              <p class="text-sm font-medium text-gray-700">Amount</p>
              <p class="mt-1 text-sm text-gray-900">{{ $client->commission_amount ?? '—' }}</p>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-700">Type</p>
              <p class="mt-1 text-sm text-gray-900">{{ $client->commission_type?->value ?? '—' }}</p>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-6">
            <div>
              <p class="text-sm font-medium text-gray-700">Discount Amount</p>
              <p class="mt-1 text-sm text-gray-900">{{ $client->discount ?? '—' }}</p>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-700">Discount Type</p>
              <p class="mt-1 text-sm text-gray-900">{{ $client->discount_type?->value ?? '—' }}</p>
            </div>
          </div>
        </div>

        {{-- Contact Person --}}
        <div class="space-y-6">
          <h2 class="text-lg font-medium text-gray-900">Contact Person</h2>

          <div>
            <p class="text-sm font-medium text-gray-700">Name</p>
            <p class="mt-1 text-sm text-gray-900">{{ $client->contact_person_name ?? '—' }}</p>
          </div>

          <div class="grid grid-cols-2 gap-6">
            <div>
              <p class="text-sm font-medium text-gray-700">Email</p>
              <p class="mt-1 text-sm text-gray-900">{{ $client->contact_person_email ?? '—' }}</p>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-700">Phone</p>
              <p class="mt-1 text-sm text-gray-900">{{ $client->contact_person_phone ?? '—' }}</p>
            </div>
          </div>
        </div>

        <div class="flex items-center gap-4">
          <a href="{{ route('clients.edit', $client) }}" class="rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 focus:outline-none focus:ring-4 focus:ring-gray-200">
            Edit Client
          </a>
          <a href="{{ route('clients.index') }}" class="text-sm font-medium text-gray-700 hover:text-gray-900">
            Back to Clients
          </a>
        </div>
      </div>
    </div>
  </main>
@endsection
