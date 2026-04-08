@extends('layouts.main')

@section('content')
  <main class="flex-1 bg-white">
    <div class="px-12 py-10">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-900">Placements</h1>
        <a href="{{ route('placements.create') }}" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 focus:outline-none focus:ring-4 focus:ring-gray-200">
          Add Placement
        </a>
      </div>
      <div class="mt-6 h-px w-full bg-gray-100"></div>

      @if(session('success'))
        <div class="mt-6 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
          {{ session('success') }}
        </div>
      @endif

      <div class="mt-6">
        <table class="min-w-full divide-y divide-gray-200">
          <thead>
            <tr>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Name</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Platform</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Type</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Description</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Price</th>
              <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            @forelse($placements as $placement)
              <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm text-gray-900">{{ $placement->name }}</td>
                <td class="px-4 py-3 text-sm text-gray-600">{{ $placement->platform->name ?? '—' }}</td>
                <td class="px-4 py-3 text-sm text-gray-600">{{ $placement->type?->label() ?? '—' }}</td>
                <td class="px-4 py-3 text-sm text-gray-600">{{ Str::limit($placement->description, 50) ?? '—' }}</td>
                <td class="px-4 py-3 text-sm text-gray-600">MUR {{ number_format($placement->price) }}</td>
                <td class="px-4 py-3 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <a href="{{ route('placements.show', $placement) }}" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                      View
                    </a>
                    <a href="{{ route('placements.edit', $placement) }}" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                      Edit
                    </a>
                    <form action="{{ route('placements.destroy', $placement) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this placement?')">
                      @csrf
                      @method('DELETE')
                      <button type="submit" class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-700 hover:bg-red-50">
                        Delete
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-500">
                  No placements found. Click "Add Placement" to create one.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </main>
@endsection
