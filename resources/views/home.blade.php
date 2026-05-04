@extends('layouts.main')

@section('title', 'Dashboard • Digital Bookings')

@section('content')
  <x-ls.page>
    <x-ls.page-header title="Dashboard" :subtitle="'Financial year ' . $financialYearLabel" />

    <x-ls.flash />

    @forelse($platformStats as $stats)
      @include('partials.dashboard-platform-section', [
        'platform' => $stats['platform'],
        'stats' => $stats,
        'financialYearLabel' => $financialYearLabel,
        'previousFinancialYearLabel' => $previousFinancialYearLabel,
        'financialYearStartDate' => $financialYearStartDate,
      ])
    @empty
      <p class="mt-8 text-sm text-ls-text-muted">No platforms available. Add a platform to see the dashboard.</p>
    @endforelse
  </x-ls.page>
@endsection
