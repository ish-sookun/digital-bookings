@extends('layouts.main')

@section('content')
  <main class="flex-1 bg-white">
    <div class="px-12 py-10">
      <h1 class="text-2xl font-semibold text-gray-900">My Profile</h1>
      <div class="mt-6 h-px w-full bg-gray-100"></div>

      @if(session('success'))
        <div class="mt-6 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
          {{ session('success') }}
        </div>
      @endif

      @if(session('error'))
        <div class="mt-6 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
          {{ session('error') }}
        </div>
      @endif

      <div class="mt-8 max-w-2xl space-y-8">
        {{-- Personal Details --}}
        <div class="space-y-6">
          <h2 class="text-lg font-medium text-gray-900">Personal Details</h2>

          @if($user->role === 'admin')
            <form action="{{ route('profile.update') }}" method="POST" class="space-y-6">
              @csrf
              @method('PUT')

              <div class="grid grid-cols-2 gap-6">
                <div>
                  <label for="firstname" class="block text-sm font-medium text-gray-700">First Name</label>
                  <div class="mt-2">
                    <input type="text" name="firstname" id="firstname" value="{{ old('firstname', $user->firstname) }}" required
                      class="block w-full rounded-lg border @error('firstname') border-red-500 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-100" />
                  </div>
                  @error('firstname')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                  @enderror
                </div>
                <div>
                  <label for="lastname" class="block text-sm font-medium text-gray-700">Last Name</label>
                  <div class="mt-2">
                    <input type="text" name="lastname" id="lastname" value="{{ old('lastname', $user->lastname) }}" required
                      class="block w-full rounded-lg border @error('lastname') border-red-500 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-100" />
                  </div>
                  @error('lastname')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                  @enderror
                </div>
              </div>

              <div class="grid grid-cols-2 gap-6">
                <div>
                  <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                  <div class="mt-2">
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required
                      class="block w-full rounded-lg border @error('email') border-red-500 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-100" />
                  </div>
                  @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                  @enderror
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-700">Role</p>
                  <p class="mt-3 text-sm text-gray-900">{{ $user->role ?? '—' }}</p>
                </div>
              </div>

              <div>
                <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 focus:outline-none focus:ring-4 focus:ring-gray-200">
                  Update Profile
                </button>
              </div>
            </form>
          @else
            <div class="grid grid-cols-2 gap-6">
              <div>
                <p class="text-sm font-medium text-gray-700">First Name</p>
                <p class="mt-1 text-sm text-gray-900">{{ $user->firstname }}</p>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-700">Last Name</p>
                <p class="mt-1 text-sm text-gray-900">{{ $user->lastname }}</p>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-6">
              <div>
                <p class="text-sm font-medium text-gray-700">Email</p>
                <p class="mt-1 text-sm text-gray-900">{{ $user->email }}</p>
              </div>
              <div>
                <p class="text-sm font-medium text-gray-700">Role</p>
                <p class="mt-1 text-sm text-gray-900">{{ $user->role ?? '—' }}</p>
              </div>
            </div>
          @endif
        </div>

        {{-- Change Password --}}
        <div class="space-y-6">
          <h2 class="text-lg font-medium text-gray-900">Change Password</h2>

          <form action="{{ route('profile.password') }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            <div>
              <label for="current_password" class="block text-sm font-medium text-gray-700">Current Password</label>
              <div class="mt-2">
                <input type="password" name="current_password" id="current_password" required
                  class="block w-full rounded-lg border @error('current_password') border-red-500 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-100" />
              </div>
              @error('current_password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
              @enderror
            </div>

            <div>
              <label for="password" class="block text-sm font-medium text-gray-700">New Password</label>
              <div class="mt-2">
                <input type="password" name="password" id="password" required
                  class="block w-full rounded-lg border @error('password') border-red-500 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-100" />
              </div>
              <p class="mt-1 text-xs text-gray-500">Minimum 8 characters, including an uppercase letter, a number, and a symbol (.,$,_,!,#).</p>
              @error('password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
              @enderror
            </div>

            <div>
              <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirm New Password</label>
              <div class="mt-2">
                <input type="password" name="password_confirmation" id="password_confirmation" required
                  class="block w-full rounded-lg border bg-white px-4 py-2.5 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-100 border-gray-200" />
              </div>
            </div>

            <div>
              <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 focus:outline-none focus:ring-4 focus:ring-gray-200">
                Update Password
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </main>
@endsection
