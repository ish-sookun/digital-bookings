@extends('layouts.main')

@section('title', 'Placement • Digital Bookings')

@section('content')
  <x-ls.page>
    <x-ls.page-header :title="$placement->name" :back="route('placements.index')" />

    <div class="mt-8 max-w-2xl space-y-6">
      <div>
        <p class="text-sm font-medium text-ls-text">Name</p>
        <p class="mt-1 text-sm text-ls-text-muted">{{ $placement->name }}</p>
      </div>

      <div>
        <p class="text-sm font-medium text-ls-text">Platform</p>
        <p class="mt-1 text-sm text-ls-text-muted">{{ $placement->platform->name ?? '—' }}</p>
      </div>

      <div>
        <p class="text-sm font-medium text-ls-text">Type</p>
        <p class="mt-1 text-sm text-ls-text-muted">{{ $placement->type?->label() ?? '—' }}</p>
      </div>

      <div>
        <p class="text-sm font-medium text-ls-text">Description</p>
        <p class="mt-1 text-sm text-ls-text-muted">{{ $placement->description ?? '—' }}</p>
      </div>

      <div>
        <p class="text-sm font-medium text-ls-text">Price</p>
        <p class="mt-1 text-sm text-ls-text-muted">MUR {{ number_format($placement->price) }}</p>
      </div>

      <div class="flex items-center gap-4">
        <x-ls.button :href="route('placements.edit', $placement)" variant="primary">Edit Placement</x-ls.button>
        <x-ls.button :href="route('placements.index')" variant="ghost">Back to Placements</x-ls.button>
      </div>
    </div>
  </x-ls.page>
@endsection
