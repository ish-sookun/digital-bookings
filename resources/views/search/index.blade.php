@extends('layouts.main')

@php
  $typeLabels = [
    'reservation' => 'Reservation Reference',
    'client' => 'Client Name',
  ];
  $typeLabel = $typeLabels[$type] ?? 'Reservation Reference';
@endphp

@section('title', 'Search Results • Digital Bookings')

@section('content')
  <x-ls.page>
    <x-ls.page-header title="Search Results" />

    <x-ls.flash />

    <div class="text-sm text-ls-text-muted">
      @if($query === '')
        Enter a search query from the sidebar Search button to get started.
      @else
        Searching for <span class="font-medium text-ls-text">"{{ $query }}"</span> in <span class="font-medium text-ls-text">{{ $typeLabel }}</span>
        @if($results)
          — {{ $results->total() }} {{ Str::plural('result', $results->total()) }} found
        @endif
      @endif
    </div>

    @if($results && $results->total() > 0)
      <div>
        <table class="ls-table">
          <thead>
            <tr>
              @if($type === 'reservation')
                <th>Reference</th>
                <th>Client</th>
                <th>Product</th>
                <th>Platform</th>
                <th>Placement</th>
                <th>Gross Amount</th>
                <th class="text-right">Actions</th>
              @else
                <th>Company</th>
                <th>BRN</th>
                <th>Phone</th>
                <th>Contact Person</th>
                <th class="text-right">Actions</th>
              @endif
            </tr>
          </thead>
          <tbody>
            @foreach($results as $row)
              <tr>
                @if($type === 'reservation')
                  <td class="font-mono text-ls-text-muted">{{ $row->id }}</td>
                  <td class="text-ls-text">{{ $row->client->company_name }}</td>
                  <td class="text-ls-text-muted">{{ $row->product }}</td>
                  <td class="text-ls-text-muted">{{ $row->platform?->name ?? '—' }}</td>
                  <td class="text-ls-text-muted">{{ $row->placement->name }}</td>
                  <td class="text-ls-text-muted">MUR {{ number_format($row->gross_amount, 2) }}</td>
                  <td class="text-right">
                    <x-ls.button :href="route('reservations.show', $row)" variant="outline" size="sm">
                      View
                    </x-ls.button>
                  </td>
                @else
                  <td class="text-ls-text">{{ $row->company_name }}</td>
                  <td class="text-ls-text-muted">{{ $row->brn }}</td>
                  <td class="text-ls-text-muted">{{ $row->phone }}</td>
                  <td class="text-ls-text-muted">{{ $row->contact_person_name ?? '—' }}</td>
                  <td class="text-right">
                    <x-ls.button :href="route('clients.show', $row)" variant="outline" size="sm">
                      View
                    </x-ls.button>
                  </td>
                @endif
              </tr>
            @endforeach
          </tbody>
        </table>

        @if($results->hasPages())
          <div class="mt-6">
            {{ $results->links() }}
          </div>
        @endif
      </div>
    @elseif($results)
      <div class="mt-10 text-center text-sm text-ls-text-muted">
        No results found for "{{ $query }}" in {{ $typeLabel }}.
      </div>
    @endif
  </x-ls.page>
@endsection
