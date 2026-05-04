@extends('layouts.main')

@section('title', 'My Profile • Digital Bookings')

@section('content')
  <x-ls.page>
    <x-ls.page-header title="My Profile" />

    <div class="mt-6">
      <x-ls.flash />
    </div>

    <div class="mt-8 max-w-2xl space-y-8">
      {{-- Personal Details --}}
      <x-ls.section title="Personal Details">
        @if($user->role === 'admin')
          <form action="{{ route('profile.update') }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-2 gap-6">
              <x-ls.input-field name="firstname" label="First Name" :value="old('firstname', $user->firstname)" required />
              <x-ls.input-field name="lastname" label="Last Name" :value="old('lastname', $user->lastname)" required />
            </div>

            <div class="grid grid-cols-2 gap-6">
              <x-ls.input-field name="email" type="email" label="Email" :value="old('email', $user->email)" required />
              <div>
                <p class="text-sm font-medium text-ls-text">Role</p>
                <p class="mt-3 text-sm text-ls-text">{{ $user->role ?? '—' }}</p>
              </div>
            </div>

            <div>
              <x-ls.button type="submit" variant="primary">Update Profile</x-ls.button>
            </div>
          </form>
        @else
          <div class="grid grid-cols-2 gap-6">
            <div>
              <p class="text-sm font-medium text-ls-text">First Name</p>
              <p class="mt-1 text-sm text-ls-text-muted">{{ $user->firstname }}</p>
            </div>
            <div>
              <p class="text-sm font-medium text-ls-text">Last Name</p>
              <p class="mt-1 text-sm text-ls-text-muted">{{ $user->lastname }}</p>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-6">
            <div>
              <p class="text-sm font-medium text-ls-text">Email</p>
              <p class="mt-1 text-sm text-ls-text-muted">{{ $user->email }}</p>
            </div>
            <div>
              <p class="text-sm font-medium text-ls-text">Role</p>
              <p class="mt-1 text-sm text-ls-text-muted">{{ $user->role ?? '—' }}</p>
            </div>
          </div>
        @endif
      </x-ls.section>

      {{-- Change Password --}}
      <x-ls.section title="Change Password">
        <form action="{{ route('profile.password') }}" method="POST" class="space-y-6">
          @csrf
          @method('PUT')

          <x-ls.input-field
            name="current_password"
            type="password"
            label="Current Password"
            required
          />

          <x-ls.input-field
            name="password"
            type="password"
            label="New Password"
            required
            hint="Minimum 8 characters, including an uppercase letter, a number, and a symbol (.,$,_,!,#)."
          />

          <x-ls.input-field
            name="password_confirmation"
            type="password"
            label="Confirm New Password"
            required
          />

          <div>
            <x-ls.button type="submit" variant="primary">Update Password</x-ls.button>
          </div>
        </form>
      </x-ls.section>
    </div>
  </x-ls.page>
@endsection
