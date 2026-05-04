@extends('layouts.main')

@section('title', 'Salespersons • Digital Bookings')

@section('content')
  <x-ls.page>
    <x-ls.page-header title="Salespersons">
      <x-slot name="actions">
        <x-ls.button :href="route('salespeople.create')" variant="primary">Add Salesperson</x-ls.button>
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
            <th>Phone</th>
            <th class="text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($salespeople as $salesperson)
            <tr>
              <td class="font-medium">{{ $salesperson->first_name }} {{ $salesperson->last_name }}</td>
              <td class="text-ls-text-muted">{{ $salesperson->email }}</td>
              <td class="text-ls-text-muted">{{ $salesperson->phone }}</td>
              <td>
                <div class="flex items-center justify-end gap-2">
                  <x-ls.button :href="route('salespeople.edit', $salesperson)" variant="outline" size="sm">Edit</x-ls.button>
                  <form action="{{ route('salespeople.destroy', $salesperson) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this salesperson?')">
                    @csrf
                    @method('DELETE')
                    <x-ls.button type="submit" variant="danger" size="sm">Delete</x-ls.button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="px-4 py-10 text-center text-sm text-ls-text-muted">
                No salespeople found. Click "Add Salesperson" to create one.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </x-ls.page>
@endsection
