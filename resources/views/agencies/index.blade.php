@extends('layouts.main')

@section('content')
  <main class="flex-1 bg-white">
    <div class="px-12 py-10">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-900">Agencies</h1>
        <a href="{{ route('agencies.create') }}" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 focus:outline-none focus:ring-4 focus:ring-gray-200">
          Add Agency
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
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Company</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">BRN</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Phone</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Contact Person</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Commission</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Discount</th>
              <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            @forelse($agencies as $agency)
              <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm text-gray-900">{{ $agency->company_name }}</td>
                <td class="px-4 py-3 text-sm text-gray-600">{{ $agency->brn }}</td>
                <td class="px-4 py-3 text-sm text-gray-600">{{ $agency->phone }}</td>
                <td class="px-4 py-3 text-sm text-gray-600">{{ $agency->contact_person_name ?? '—' }}</td>
                <td class="px-4 py-3 text-sm text-gray-600">
                  @if($agency->commission_amount && $agency->commission_type)
                    {{ $agency->commission_amount }}{{ $agency->commission_type->value }}
                  @else
                    —
                  @endif
                </td>
                <td class="px-4 py-3 text-sm text-gray-600">
                  @if($agency->discount && $agency->discount_type)
                    {{ $agency->discount }}{{ $agency->discount_type->value }}
                  @else
                    —
                  @endif
                </td>
                <td class="px-4 py-3 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <a href="{{ route('agencies.show', $agency) }}" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                      View
                    </a>
                    <a href="{{ route('agencies.edit', $agency) }}" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                      Edit
                    </a>
                    <form action="{{ route('agencies.destroy', $agency) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this agency?')">
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
                <td colspan="7" class="px-4 py-10 text-center text-sm text-gray-500">
                  No agencies found. Click "Add Agency" to create one.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </main>
@endsection
