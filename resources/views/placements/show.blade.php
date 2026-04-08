@extends('layouts.main')

@section('content')
  <main class="flex-1 bg-white">
    <div class="px-12 py-10">
      <div class="flex items-center gap-4">
        <a href="{{ route('placements.index') }}" class="text-gray-400 hover:text-gray-600">
          <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M17 10a.75.75 0 0 1-.75.75H5.612l4.158 3.96a.75.75 0 1 1-1.04 1.08l-5.5-5.25a.75.75 0 0 1 0-1.08l5.5-5.25a.75.75 0 0 1 1.04 1.08L5.612 9.25H16.25A.75.75 0 0 1 17 10Z" clip-rule="evenodd" />
          </svg>
        </a>
        <h1 class="text-2xl font-semibold text-gray-900">{{ $placement->name }}</h1>
      </div>
      <div class="mt-6 h-px w-full bg-gray-100"></div>

      <div class="mt-8 max-w-2xl space-y-6">
        <div>
          <p class="text-sm font-medium text-gray-700">Name</p>
          <p class="mt-1 text-sm text-gray-900">{{ $placement->name }}</p>
        </div>

        <div>
          <p class="text-sm font-medium text-gray-700">Platform</p>
          <p class="mt-1 text-sm text-gray-900">{{ $placement->platform->name ?? '—' }}</p>
        </div>

        <div>
          <p class="text-sm font-medium text-gray-700">Type</p>
          <p class="mt-1 text-sm text-gray-900">{{ $placement->type?->label() ?? '—' }}</p>
        </div>

        <div>
          <p class="text-sm font-medium text-gray-700">Description</p>
          <p class="mt-1 text-sm text-gray-900">{{ $placement->description ?? '—' }}</p>
        </div>

        <div>
          <p class="text-sm font-medium text-gray-700">Price</p>
          <p class="mt-1 text-sm text-gray-900">MUR {{ number_format($placement->price) }}</p>
        </div>

        <div class="flex items-center gap-4">
          <a href="{{ route('placements.edit', $placement) }}" class="rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 focus:outline-none focus:ring-4 focus:ring-gray-200">
            Edit Placement
          </a>
          <a href="{{ route('placements.index') }}" class="text-sm font-medium text-gray-700 hover:text-gray-900">
            Back to Placements
          </a>
        </div>
      </div>
    </div>
  </main>
@endsection
