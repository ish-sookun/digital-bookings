@extends('layouts.main')

@section('content')
  <main class="flex-1 bg-white">
    <div class="px-12 py-10">
      <div class="flex items-center gap-4">
        <a href="{{ route('users.index') }}" class="text-gray-400 hover:text-gray-600">
          <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M17 10a.75.75 0 0 1-.75.75H5.612l4.158 3.96a.75.75 0 1 1-1.04 1.08l-5.5-5.25a.75.75 0 0 1 0-1.08l5.5-5.25a.75.75 0 0 1 1.04 1.08L5.612 9.25H16.25A.75.75 0 0 1 17 10Z" clip-rule="evenodd" />
          </svg>
        </a>
        <h1 class="text-2xl font-semibold text-gray-900">Add User</h1>
      </div>
      <div class="mt-6 h-px w-full bg-gray-100"></div>

      <form action="{{ route('users.store') }}" method="POST" class="mt-8 max-w-xl space-y-6">
        @csrf

        <div class="grid grid-cols-2 gap-6">
          <div>
            <label for="firstname" class="block text-sm font-medium text-gray-700">First Name</label>
            <div class="mt-2">
              <input type="text" name="firstname" id="firstname" value="{{ old('firstname') }}" required
                class="block w-full rounded-lg border @error('firstname') border-red-500 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-100" />
            </div>
            @error('firstname')
              <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
          </div>

          <div>
            <label for="lastname" class="block text-sm font-medium text-gray-700">Last Name</label>
            <div class="mt-2">
              <input type="text" name="lastname" id="lastname" value="{{ old('lastname') }}" required
                class="block w-full rounded-lg border @error('lastname') border-red-500 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-100" />
            </div>
            @error('lastname')
              <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
          </div>
        </div>

        <div>
          <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
          <div class="mt-2">
            <input type="email" name="email" id="email" value="{{ old('email') }}" required
              class="block w-full rounded-lg border @error('email') border-red-500 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-100" />
          </div>
          @error('email')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label for="role" class="block text-sm font-medium text-gray-700">Role</label>
          <div class="mt-2">
            <select name="role" id="role" required
              class="block w-full rounded-lg border @error('role') border-red-500 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-gray-900 shadow-sm focus:border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-100">
              <option value="">Select a role</option>
              @foreach($roles as $role)
                <option value="{{ $role->value }}" {{ old('role') === $role->value ? 'selected' : '' }}>
                  {{ match($role) {
                      \App\UserRole::SuperAdmin => 'Super Admin',
                      \App\UserRole::Admin => 'Admin',
                      \App\UserRole::Salesperson => 'Salesperson',
                  } }}
                </option>
              @endforeach
            </select>
          </div>
          @error('role')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
          <div class="mt-2">
            <input type="password" name="password" id="password" required
              class="block w-full rounded-lg border @error('password') border-red-500 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-100" />
          </div>
          <p class="mt-1 text-xs text-gray-500">Minimum 8 characters, including an uppercase letter, a number, and a symbol (.,$,_,!,#).</p>
          @error('password')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <div class="flex items-center gap-4">
          <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 focus:outline-none focus:ring-4 focus:ring-gray-200">
            Save User
          </button>
          <a href="{{ route('users.index') }}" class="text-sm font-medium text-gray-700 hover:text-gray-900">
            Cancel
          </a>
        </div>
      </form>
    </div>
  </main>
@endsection
