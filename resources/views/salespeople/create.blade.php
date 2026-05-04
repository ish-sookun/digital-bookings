@extends('layouts.main')

@section('title', 'Add Salesperson • Digital Bookings')

@section('content')
  <x-ls.page>
    <x-ls.page-header title="Add Salesperson" :back="route('salespeople.index')" />

    <form action="{{ route('salespeople.store') }}" method="POST" class="mt-8 max-w-xl space-y-6">
      @csrf

      <div class="grid grid-cols-2 gap-6">
        <x-ls.input-field name="first_name" label="First Name" :value="old('first_name')" required />
        <x-ls.input-field name="last_name" label="Last Name" :value="old('last_name')" required />
      </div>

      <x-ls.input-field name="email" type="email" label="Email" :value="old('email')" required />
      <x-ls.input-field name="phone" label="Phone" :value="old('phone')" required />
      <x-ls.input-field
        name="sage_salesperson_code"
        label="SAGE Salesperson Code"
        :value="old('sage_salesperson_code')"
        maxlength="50"
      />

      <div class="flex items-center gap-4">
        <x-ls.button type="submit" variant="primary">Save Salesperson</x-ls.button>
        <x-ls.button :href="route('salespeople.index')" variant="ghost">Cancel</x-ls.button>
      </div>
    </form>
  </x-ls.page>
@endsection
