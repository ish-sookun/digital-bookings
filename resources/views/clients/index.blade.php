@extends('layouts.main')

@section('title', 'Clients • Digital Bookings')

@section('content')
  <x-ls.page>
    <x-ls.page-header title="Clients">
      <x-slot name="actions">
        <x-ls.button :href="route('clients.create')" variant="primary">Add Client</x-ls.button>
      </x-slot>
    </x-ls.page-header>

    <div class="mt-6">
      <x-ls.flash />
    </div>

    <div class="mt-6">
      <table class="ls-table">
        <thead>
          <tr>
            <th>Company</th>
            <th>BRN</th>
            <th>Phone</th>
            <th>Contact Person</th>
            <th>Commission</th>
            <th>Discount</th>
            <th class="text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($clients as $client)
            <tr>
              <td class="font-medium">{{ $client->company_name }}</td>
              <td class="text-ls-text-muted">{{ $client->brn }}</td>
              <td class="text-ls-text-muted">{{ $client->phone }}</td>
              <td class="text-ls-text-muted">{{ $client->contact_person_name ?? '—' }}</td>
              <td class="text-ls-text-muted">
                @if($client->commission_amount && $client->commission_type)
                  {{ $client->commission_amount }}{{ $client->commission_type->value }}
                @else
                  —
                @endif
              </td>
              <td class="text-ls-text-muted">
                @if($client->discount && $client->discount_type)
                  {{ $client->discount }}{{ $client->discount_type->value }}
                @else
                  —
                @endif
              </td>
              <td>
                <div class="flex items-center justify-end gap-2">
                  <x-ls.button :href="route('clients.show', $client)" variant="outline" size="sm">View</x-ls.button>
                  <x-ls.button :href="route('clients.edit', $client)" variant="outline" size="sm">Edit</x-ls.button>
                  @can('delete-clients')
                    <form action="{{ route('clients.destroy', $client) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this client?')">
                      @csrf
                      @method('DELETE')
                      <x-ls.button type="submit" variant="danger" size="sm">Delete</x-ls.button>
                    </form>
                  @endcan
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="px-4 py-10 text-center text-sm text-ls-text-muted">
                No clients found. Click "Add Client" to create one.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>

      @if($clients->hasPages())
        <div class="mt-6">
          {{ $clients->links() }}
        </div>
      @endif
    </div>
  </x-ls.page>
@endsection
