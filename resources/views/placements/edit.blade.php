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
        <h1 class="text-2xl font-semibold text-gray-900">Edit Placement</h1>
      </div>
      <div class="mt-6 h-px w-full bg-gray-100"></div>

      <form action="{{ route('placements.update', $placement) }}" method="POST" class="mt-8 max-w-2xl space-y-6">
        @csrf
        @method('PUT')

        <div>
          <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
          <div class="mt-2">
            <input type="text" name="name" id="name" value="{{ old('name', $placement->name) }}" required
              class="block w-full rounded-lg border @error('name') border-red-500 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-100" />
          </div>
          @error('name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
          <div class="mt-2">
            <textarea name="description" id="description" rows="4"
              class="block w-full rounded-lg border @error('description') border-red-500 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-100">{{ old('description', $placement->description) }}</textarea>
          </div>
          @error('description')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label for="platform_id" class="block text-sm font-medium text-gray-700">Platform</label>
          <div class="mt-2">
            <select name="platform_id" id="platform_id"
              class="block w-full rounded-lg border @error('platform_id') border-red-500 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-gray-900 shadow-sm focus:border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-100">
              <option value="">— None —</option>
              @foreach($platforms as $platform)
                <option value="{{ $platform->id }}" {{ old('platform_id', $placement->platform_id) == $platform->id ? 'selected' : '' }}>{{ $platform->name }}</option>
              @endforeach
            </select>
          </div>
          @error('platform_id')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label for="type" class="block text-sm font-medium text-gray-700">Type</label>
          <div class="mt-2">
            @php
              $currentType = old('type', $placement->type?->value);
            @endphp
            <select name="type" id="type" required
              class="block w-full rounded-lg border @error('type') border-red-500 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-gray-900 shadow-sm focus:border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-100">
              @foreach(\App\PlacementType::cases() as $type)
                <option value="{{ $type->value }}" {{ $currentType === $type->value ? 'selected' : '' }}>{{ $type->label() }}</option>
              @endforeach
            </select>
          </div>
          @error('type')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <div>
          <label for="price" class="block text-sm font-medium text-gray-700">Price (MUR)</label>
          <div class="mt-2">
            <input name="price" id="price" value="{{ old('price', $placement->price) }}" min="0" required
              class="block w-full rounded-lg border @error('price') border-red-500 @else border-gray-200 @enderror bg-white px-4 py-2.5 text-gray-900 shadow-sm placeholder:text-gray-400 focus:border-gray-300 focus:outline-none focus:ring-4 focus:ring-gray-100" />
          </div>
          @error('price')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
          @enderror
        </div>

        <div class="flex items-center gap-4">
          <button type="submit" class="rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 focus:outline-none focus:ring-4 focus:ring-gray-200">
            Update Placement
          </button>
          <a href="{{ route('placements.index') }}" class="text-sm font-medium text-gray-700 hover:text-gray-900">
            Cancel
          </a>
        </div>
      </form>
    </div>
  </main>
@endsection
