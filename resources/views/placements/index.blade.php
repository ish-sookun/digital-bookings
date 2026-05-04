@extends('layouts.main')

@section('title', 'Placements • Digital Bookings')

@section('content')
  <x-ls.page>
    <x-ls.page-header title="Placements">
      <x-slot name="actions">
        <x-ls.button :href="route('placements.create')" variant="primary">Add Placement</x-ls.button>
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
            <th>Platform</th>
            <th>Type</th>
            <th>Description</th>
            <th>Price</th>
            <th class="text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($placements as $placement)
            <tr>
              <td class="font-medium">{{ $placement->name }}</td>
              <td class="text-ls-text-muted">{{ $placement->platform->name ?? '—' }}</td>
              <td class="text-ls-text-muted">{{ $placement->type?->label() ?? '—' }}</td>
              <td class="text-ls-text-muted">{{ Str::limit($placement->description, 50) ?? '—' }}</td>
              <td class="text-ls-text-muted">MUR {{ number_format($placement->price) }}</td>
              <td>
                <div class="flex items-center justify-end gap-2">
                  <x-ls.button :href="route('placements.show', $placement)" variant="outline" size="sm">View</x-ls.button>
                  <x-ls.button :href="route('placements.edit', $placement)" variant="outline" size="sm">Edit</x-ls.button>
                  <form action="{{ route('placements.destroy', $placement) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this placement?')">
                    @csrf
                    @method('DELETE')
                    <x-ls.button type="submit" variant="danger" size="sm">Delete</x-ls.button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="px-4 py-10 text-center text-sm text-ls-text-muted">
                No placements found. Click "Add Placement" to create one.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </x-ls.page>
@endsection
