@extends('layouts.main')

@section('title', 'Add Client • Digital Bookings')

@section('content')
  <x-ls.page>
    <x-ls.page-header title="Add Client" :back="route('clients.index')" />

    <form action="{{ route('clients.store') }}" method="POST" enctype="multipart/form-data" class="mt-8 max-w-2xl space-y-8">
      @csrf

      {{-- Company Details --}}
      <x-ls.section title="Company Details">
        <div class="ls-field">
          <label for="company_logo">Company Logo</label>
          <input type="file" name="company_logo" id="company_logo" accept=".jpeg,.jpg,.png"
            class="block w-full text-sm text-ls-text-muted file:mr-4 file:rounded-md file:border-0 file:bg-ls-surface-muted file:px-4 file:py-2 file:text-sm file:font-medium file:text-ls-text hover:file:bg-ls-cream-dark" />
          @error('company_logo')
            <span class="hint" style="color: var(--ls-danger-text);">{{ $message }}</span>
          @else
            <span class="hint">JPEG or PNG, max 1 MB.</span>
          @enderror
        </div>

        <x-ls.input-field name="company_name" label="Company Name" :value="old('company_name')" required />

        <div class="grid grid-cols-2 gap-6">
          <x-ls.input-field name="brn" label="BRN" :value="old('brn')" required />
          <x-ls.input-field name="phone" label="Phone" :value="old('phone')" required />
        </div>

        <x-ls.input-field name="address" label="Address" :value="old('address')" required />

        <div class="grid grid-cols-2 gap-6">
          <x-ls.input-field name="vat_number" label="VAT Number" :value="old('vat_number')" />

          <div class="flex items-end pb-1">
            <x-ls.check name="vat_exempt" label="VAT Exempt" :checked="(bool) old('vat_exempt')" />
          </div>
        </div>

        <x-ls.input-field name="sage_client_code" label="SAGE Client Code" :value="old('sage_client_code')" maxlength="50" />
      </x-ls.section>

      @can('edit-financials')
      {{-- Commission --}}
      <x-ls.section title="Commission">
        <div class="grid grid-cols-2 gap-6">
          <x-ls.input-field name="commission_amount" label="Amount" :value="old('commission_amount')" min="0" />

          <x-ls.select-field
            name="commission_type"
            label="Type"
            placeholder="Select type"
            :options="collect($commissionTypes)->mapWithKeys(fn($t) => [$t->value => $t->value])->toArray()"
            :selected="old('commission_type')"
          />
        </div>

        <div class="grid grid-cols-2 gap-6">
          <x-ls.input-field name="discount" label="Discount Amount" :value="old('discount')" min="0" />

          <x-ls.select-field
            name="discount_type"
            label="Discount Type"
            placeholder="Select type"
            :options="collect($discountTypes)->mapWithKeys(fn($t) => [$t->value => $t->value])->toArray()"
            :selected="old('discount_type')"
          />
        </div>
      </x-ls.section>
      @endcan

      {{-- Contact Person --}}
      <x-ls.section title="Contact Person">
        <x-ls.input-field name="contact_person_name" label="Name" :value="old('contact_person_name')" />

        <div class="grid grid-cols-2 gap-6">
          <x-ls.input-field name="contact_person_email" type="email" label="Email" :value="old('contact_person_email')" />
          <x-ls.input-field name="contact_person_phone" label="Phone" :value="old('contact_person_phone')" />
        </div>
      </x-ls.section>

      <div class="flex items-center gap-4">
        <x-ls.button type="submit" variant="primary">Save Client</x-ls.button>
        <x-ls.button :href="route('clients.index')" variant="ghost">Cancel</x-ls.button>
      </div>
    </form>
  </x-ls.page>
@endsection
