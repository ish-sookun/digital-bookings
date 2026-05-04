@extends('layouts.main')

@section('title', 'Edit User • Digital Bookings')

@section('content')
  <x-ls.page>
    <x-ls.page-header title="Edit User" :back="route('users.index')" />

    <form action="{{ route('users.update', $user) }}" method="POST" class="mt-8 max-w-xl space-y-6">
      @csrf
      @method('PUT')

      <div class="grid grid-cols-2 gap-6">
        <x-ls.input-field name="firstname" label="First Name" :value="old('firstname', $user->firstname)" required />
        <x-ls.input-field name="lastname" label="Last Name" :value="old('lastname', $user->lastname)" required />
      </div>

      <x-ls.input-field name="email" type="email" label="Email" :value="old('email', $user->email)" required />

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
        :selected="old('role', $user->role->value)"
        required
      />

      <x-ls.input-field
        name="password"
        type="password"
        label="Password"
        placeholder="Leave blank to keep the current password"
        hint="Minimum 8 characters, including an uppercase letter, a number, and a symbol (.,$,_,!,#)."
      />

      <div class="flex items-center gap-4">
        <x-ls.button type="submit" variant="primary">Update User</x-ls.button>
        <x-ls.button :href="route('users.index')" variant="ghost">Cancel</x-ls.button>
      </div>
    </form>
  </x-ls.page>
@endsection
