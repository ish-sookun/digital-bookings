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
        <h1 class="text-2xl font-semibold text-gray-900">Edit Client</h1>
      </div>
      <div class="mt-6 h-px w-full bg-gray-100"></div>

      <form action="{{ route('clients.update', $client) }}" method="POST" enctype="multipart/form-data" class="mt-8 max-w-2xl space-y-8">
        @csrf
        @method('PUT')

        {{-- Company Details --}}
        <div class="space-y-6">
          <h2 class="text-lg font-medium text-gray-900">Company Details</h2>

          <div>
            <label for="company_logo" class="block text-sm font-medium text-gray-700">Company Logo</label>
            <div class="mt-2">
              @if($client->company_logo)
                <div class="mb-3 flex items-center gap-4">
                  <img src="{{ Storage::url($client->company_logo) }}" alt="{{ $client->company_name }}" class="max-h-16 rounded-lg object-contain" />
                  <span class="text-sm text-gray-500">Current logo</span>
                </div>
              @endif
              <input type="file" name="company_logo" id="company_logo" accept=".jpeg,.jpg,.png"
                class="block w-full text-sm text-gray-600 file:mr-4 file:rounded-lg file:border-0 file:bg-gray-100 file:px-4 file:py-2.5 file:text-sm file:font-medium file:text-gray-700 hover:file:bg-gray-200" />
            </div>
            <p class="mt-1 text-xs text-gray-500">JPEG or PNG, max 1 MB.{{ $client->company_logo ? ' Upload a new file to replace the current logo.' : '' }}</p>
            @error('company_logo')
              <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
          </div>

          <div>
            <label for="company_name" class="block text-sm font-medium text-gray-700">Company Name</label>
            <div class="mt-2">
              <input type="text" name="company_name" id="company_name" value="{{ old('company_name', $client->company_name) }}" required
                class="block w-full rounded-lg border @error('company_name') border-red-500 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-100" />
            </div>
            @error('company_name')
              <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
          </div>

          <div class="grid grid-cols-2 gap-6">
            <div>
              <label for="brn" class="block text-sm font-medium text-gray-700">BRN</label>
              <div class="mt-2">
                <input type="text" name="brn" id="brn" value="{{ old('brn', $client->brn) }}" required
                  class="block w-full rounded-lg border @error('brn') border-red-500 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-100" />
              </div>
              @error('brn')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
              @enderror
            </div>

            <div>
              <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
              <div class="mt-2">
                <input type="text" name="phone" id="phone" value="{{ old('phone', $client->phone) }}" required
                  class="block w-full rounded-lg border @error('phone') border-red-500 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-100" />
              </div>
              @error('phone')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
              @enderror
            </div>
          </div>

          <div>
            <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
            <div class="mt-2">
              <input type="text" name="address" id="address" value="{{ old('address', $client->address) }}" required
                class="block w-full rounded-lg border @error('address') border-red-500 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-100" />
            </div>
            @error('address')
              <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
          </div>

          <div class="grid grid-cols-2 gap-6">
            <div>
              <label for="vat_number" class="block text-sm font-medium text-gray-700">VAT Number</label>
              <div class="mt-2">
                <input name="vat_number" id="vat_number" value="{{ old('vat_number', $client->vat_number) }}"
                  class="block w-full rounded-lg border @error('vat_number') border-red-500 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-100" />
              </div>
              @error('vat_number')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
              @enderror
            </div>

            <div class="flex items-end pb-1">
              <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                <input type="hidden" name="vat_exempt" value="0" />
                <input type="checkbox" name="vat_exempt" value="1" {{ old('vat_exempt', $client->vat_exempt) ? 'checked' : '' }}
                  class="h-4 w-4 rounded border-gray-300 text-gray-900 focus:ring-gray-200" />
                VAT Exempt
              </label>
            </div>
          </div>
        </div>

        @can('edit-financials')
        {{-- Commission --}}
        <div class="space-y-6">
          <h2 class="text-lg font-medium text-gray-900">Commission</h2>

          <div class="grid grid-cols-2 gap-6">
            <div>
              <label for="commission_amount" class="block text-sm font-medium text-gray-700">Amount</label>
              <div class="mt-2">
                <input name="commission_amount" id="commission_amount" value="{{ old('commission_amount', $client->commission_amount) }}" min="0"
                  class="block w-full rounded-lg border @error('commission_amount') border-red-500 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-100" />
              </div>
              @error('commission_amount')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
              @enderror
            </div>

            <div>
              <label for="commission_type" class="block text-sm font-medium text-gray-700">Type</label>
              <div class="mt-2">
                <select name="commission_type" id="commission_type"
                  class="block w-full rounded-lg border @error('commission_type') border-red-500 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-gray-900 shadow-sm focus:border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-100">
                  <option value="">Select type</option>
                  @foreach($commissionTypes as $type)
                    <option value="{{ $type->value }}" {{ old('commission_type', $client->commission_type?->value) === $type->value ? 'selected' : '' }}>{{ $type->value }}</option>
                  @endforeach
                </select>
              </div>
              @error('commission_type')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
              @enderror
            </div>
          </div>

          <div class="grid grid-cols-2 gap-6">
            <div>
              <label for="discount" class="block text-sm font-medium text-gray-700">Discount Amount</label>
              <div class="mt-2">
                <input name="discount" id="discount" value="{{ old('discount', $client->discount) }}" min="0"
                  class="block w-full rounded-lg border @error('discount') border-red-500 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-100" />
              </div>
              @error('discount')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
              @enderror
            </div>

            <div>
              <label for="discount_type" class="block text-sm font-medium text-gray-700">Discount Type</label>
              <div class="mt-2">
                <select name="discount_type" id="discount_type"
                  class="block w-full rounded-lg border @error('discount_type') border-red-500 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-gray-900 shadow-sm focus:border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-100">
                  <option value="">Select type</option>
                  @foreach($discountTypes as $type)
                    <option value="{{ $type->value }}" {{ old('discount_type', $client->discount_type?->value) === $type->value ? 'selected' : '' }}>{{ $type->value }}</option>
                  @endforeach
                </select>
              </div>
              @error('discount_type')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
              @enderror
            </div>
          </div>
        </div>
        @endcan

        {{-- Contact Person --}}
        <div class="space-y-6">
          <h2 class="text-lg font-medium text-gray-900">Contact Person</h2>

          <div>
            <label for="contact_person_name" class="block text-sm font-medium text-gray-700">Name</label>
            <div class="mt-2">
              <input type="text" name="contact_person_name" id="contact_person_name" value="{{ old('contact_person_name', $client->contact_person_name) }}"
                class="block w-full rounded-lg border @error('contact_person_name') border-red-500 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-100" />
            </div>
            @error('contact_person_name')
              <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
          </div>

          <div class="grid grid-cols-2 gap-6">
            <div>
              <label for="contact_person_email" class="block text-sm font-medium text-gray-700">Email</label>
              <div class="mt-2">
                <input type="email" name="contact_person_email" id="contact_person_email" value="{{ old('contact_person_email', $client->contact_person_email) }}"
                  class="block w-full rounded-lg border @error('contact_person_email') border-red-500 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-100" />
              </div>
              @error('contact_person_email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
              @enderror
            </div>

            <div>
              <label for="contact_person_phone" class="block text-sm font-medium text-gray-700">Phone</label>
              <div class="mt-2">
                <input type="text" name="contact_person_phone" id="contact_person_phone" value="{{ old('contact_person_phone', $client->contact_person_phone) }}"
                  class="block w-full rounded-lg border @error('contact_person_phone') border-red-500 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-100" />
              </div>
              @error('contact_person_phone')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
              @enderror
            </div>
          </div>
        </div>

        <div class="flex items-center gap-4">
          <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 focus:outline-none focus:ring-4 focus:ring-gray-200">
            Update Client
          </button>
          <a href="{{ route('clients.index') }}" class="text-sm font-medium text-gray-700 hover:text-gray-900">
            Cancel
          </a>
        </div>
      </form>
    </div>
  </main>
@endsection
