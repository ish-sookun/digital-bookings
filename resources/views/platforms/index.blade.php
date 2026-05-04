@extends('layouts.main')

@section('title', 'Platforms • Digital Bookings')

@section('content')
  <x-ls.page>
    <x-ls.page-header title="Platforms">
      <x-slot name="actions">
        <x-ls.button :href="route('platforms.create')" variant="primary">Add Platform</x-ls.button>
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
            <th>Description</th>
            <th class="text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($platforms as $platform)
            <tr>
              <td class="font-medium">{{ $platform->name }}</td>
              <td class="text-ls-text-muted">{{ Str::limit($platform->description, 50) ?? '—' }}</td>
              <td>
                <div class="flex items-center justify-end gap-2">
                  <x-ls.button :href="route('platforms.show', $platform)" variant="outline" size="sm">View</x-ls.button>
                  <x-ls.button :href="route('platforms.edit', $platform)" variant="outline" size="sm">Edit</x-ls.button>
                  <form action="{{ route('platforms.destroy', $platform) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this platform?')">
                    @csrf
                    @method('DELETE')
                    <x-ls.button type="submit" variant="danger" size="sm">Delete</x-ls.button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="3" class="px-4 py-10 text-center text-sm text-ls-text-muted">
                No platforms found. Click "Add Platform" to create one.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </x-ls.page>
@endsection
