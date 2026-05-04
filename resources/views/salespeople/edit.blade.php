@extends('layouts.main')

@section('title', 'Edit Salesperson • Digital Bookings')

@section('content')
  <x-ls.page>
    <x-ls.page-header title="Edit Salesperson" :back="route('salespeople.index')" />

    <form action="{{ route('salespeople.update', $salesperson) }}" method="POST" class="mt-8 max-w-xl space-y-6">
      @csrf
      @method('PUT')

      <div class="grid grid-cols-2 gap-6">
        <x-ls.input-field name="first_name" label="First Name" :value="old('first_name', $salesperson->first_name)" required />
        <x-ls.input-field name="last_name" label="Last Name" :value="old('last_name', $salesperson->last_name)" required />
      </div>

      <x-ls.input-field name="email" type="email" label="Email" :value="old('email', $salesperson->email)" required />
      <x-ls.input-field name="phone" label="Phone" :value="old('phone', $salesperson->phone)" required />
      <x-ls.input-field
        name="sage_salesperson_code"
        label="SAGE Salesperson Code"
        :value="old('sage_salesperson_code', $salesperson->sage_salesperson_code)"
        maxlength="50"
      />

      <div class="flex items-center gap-4">
        <x-ls.button type="submit" variant="primary">Update Salesperson</x-ls.button>
        <x-ls.button :href="route('salespeople.index')" variant="ghost">Cancel</x-ls.button>
      </div>
    </form>
  </x-ls.page>
@endsection
