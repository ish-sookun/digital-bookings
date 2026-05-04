@extends('layouts.main')

@section('title', 'Users • Digital Bookings')

@section('content')
  <x-ls.page>
    <x-ls.page-header title="Users">
      <x-slot name="actions">
        <x-ls.button :href="route('users.create')" variant="primary">Add User</x-ls.button>
      </x-slot>
    </x-ls.page-header>

    <div class="mt-6">
      <x-ls.flash />
    </div>

    <div class="mt-6">
      <table class="ls-table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th class="text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($users as $user)
            <tr>
              <td>{{ $user->firstname }} {{ $user->lastname }}</td>
              <td class="text-ls-text-muted">{{ $user->email }}</td>
              <td>
                @switch($user->role)
                  @case(\App\UserRole::SuperAdmin)
                    <x-ls.pill variant="info">Super Admin</x-ls.pill>
                    @break
                  @case(\App\UserRole::Admin)
                    <x-ls.pill variant="info">Admin</x-ls.pill>
                    @break
                  @case(\App\UserRole::Salesperson)
                    <x-ls.pill variant="success">Salesperson</x-ls.pill>
                    @break
                  @case(\App\UserRole::Management)
                    <x-ls.pill variant="warning">Management</x-ls.pill>
                    @break
                  @case(\App\UserRole::Finance)
                    <x-ls.pill variant="neutral">Finance</x-ls.pill>
                    @break
                @endswitch
              </td>
              <td class="text-right">
                <div class="flex items-center justify-end gap-2">
                  <x-ls.button :href="route('users.edit', $user)" variant="outline" size="sm">Edit</x-ls.button>
                  @if($user->id !== auth()->id())
                    <form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?')">
                      @csrf
                      @method('DELETE')
                      <x-ls.button type="submit" variant="danger" size="sm">Delete</x-ls.button>
                    </form>
                  @endif
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="text-center text-ls-text-muted">
                No users found. Click "Add User" to create one.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </x-ls.page>
@endsection
