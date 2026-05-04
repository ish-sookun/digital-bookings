@extends('layouts.main')

@section('title', 'Platform • Digital Bookings')

@section('content')
  <x-ls.page>
    <x-ls.page-header :title="$platform->name" :back="route('platforms.index')" />

    <div class="mt-8 max-w-2xl space-y-6">
      <div>
        <p class="text-sm font-medium text-ls-text">Name</p>
        <p class="mt-1 text-sm text-ls-text-muted">{{ $platform->name }}</p>
      </div>

      <div>
        <p class="text-sm font-medium text-ls-text">Description</p>
        <p class="mt-1 text-sm text-ls-text-muted">{{ $platform->description ?? '—' }}</p>
      </div>

      <div class="flex items-center gap-4">
        <x-ls.button :href="route('platforms.edit', $platform)" variant="primary">Edit Platform</x-ls.button>
        <x-ls.button :href="route('platforms.index')" variant="ghost">Back to Platforms</x-ls.button>
      </div>
    </div>
  </x-ls.page>
@endsection
