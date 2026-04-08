@extends('layouts.main')

@section('content')
  <main class="flex-1 bg-white">
    <div class="px-12 py-10">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-900">Reservations</h1>
        <a href="{{ route('reservations.create') }}" class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-800 focus:outline-none focus:ring-4 focus:ring-gray-200">
          Add Reservation
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
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Reference</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Status</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Client</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Product</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Platform</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Placement</th>
              <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Total to Pay</th>
              <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            @forelse($reservations as $reservation)
              <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-sm">
                  <span class="inline-flex items-center rounded-md px-2 py-1 font-mono text-xs font-medium ring-1 ring-inset {{ $reservation->status->referenceClasses() }}">
                    {{ $reservation->reference }}
                  </span>
                </td>
                <td class="px-4 py-3 text-sm text-gray-600">
                  <span class="inline-flex items-center gap-2">
                    <span class="inline-block h-2.5 w-2.5 rounded-full {{ $reservation->status->dotClasses() }}"></span>
                    {{ $reservation->status->label() }}
                  </span>
                </td>
                <td class="px-4 py-3 text-sm text-gray-900">{{ $reservation->client->company_name }}</td>
                <td class="px-4 py-3 text-sm text-gray-600">{{ $reservation->product }}</td>
                <td class="px-4 py-3 text-sm text-gray-600">{{ $reservation->platform?->name ?? '—' }}</td>
                <td class="px-4 py-3 text-sm text-gray-600">{{ $reservation->placement->name }}</td>
                <td class="px-4 py-3 text-sm text-gray-600">MUR {{ number_format($reservation->total_amount_to_pay, 2) }}</td>
                <td class="px-4 py-3 text-right">
                  <div class="flex items-center justify-end gap-2">
                    <a href="{{ route('reservations.show', $reservation) }}" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                      View
                    </a>
                    <a href="{{ route('reservations.edit', $reservation) }}" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                      Edit
                    </a>
                    <form action="{{ route('reservations.destroy', $reservation) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this reservation?')">
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
                <td colspan="8" class="px-4 py-10 text-center text-sm text-gray-500">
                  No reservations found. Click "Add Reservation" to create one.
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>

        @if($reservations->hasPages())
          <div class="mt-6">
            {{ $reservations->links() }}
          </div>
        @endif
      </div>
    </div>
  </main>
@endsection
