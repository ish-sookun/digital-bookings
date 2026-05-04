@extends('layouts.main')

@section('title', 'Add User • Digital Bookings')

@section('content')
  <x-ls.page>
    <x-ls.page-header title="Add User" :back="route('users.index')" />

    <form action="{{ route('users.store') }}" method="POST" class="mt-8 max-w-xl space-y-6">
      @csrf

      <div class="grid grid-cols-2 gap-6">
        <x-ls.input-field name="firstname" label="First Name" :value="old('firstname')" required />
        <x-ls.input-field name="lastname" label="Last Name" :value="old('lastname')" required />
      </div>

      <x-ls.input-field name="email" type="email" label="Email" :value="old('email')" required />

      @php
        $roleOptions = [];
        foreach ($roles as $role) {
          $roleOptions[$role->value] = match($role) {
              \App\UserRole::SuperAdmin => 'Super Admin',
              \App\UserRole::Admin => 'Admin',
              \App\UserRole::Salesperson => 'Salesperson',
              \App\UserRole::Management => 'Management',
              \App\UserRole::Finance => 'Finance',
          };
        }
      @endphp

      <x-ls.select-field
        name="role"
        label="Role"
        :options="$roleOptions"
        :selected="old('role')"
        placeholder="Select a role"
        required
      />

      <x-ls.input-field
        name="password"
        type="password"
        label="Password"
        required
        hint="Minimum 8 characters, including an uppercase letter, a number, and a symbol (.,$,_,!,#)."
      />

      <div class="flex items-center gap-4">
        <x-ls.button type="submit" variant="primary">Save User</x-ls.button>
        <x-ls.button :href="route('users.index')" variant="ghost">Cancel</x-ls.button>
      </div>
    </form>
  </x-ls.page>
@endsection
