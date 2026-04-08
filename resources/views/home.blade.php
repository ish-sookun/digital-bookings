@extends('layouts.main')

@section('content')
  <main class="flex-1 bg-white">
    <div class="px-12 py-10">
      <div class="flex items-end justify-between">
        <h1 class="text-2xl font-semibold text-gray-900">Dashboard</h1>
        <p class="text-sm text-gray-500">Financial year {{ $financialYearLabel }}</p>
      </div>
      <div class="mt-6 h-px w-full bg-gray-100"></div>

      @forelse($platformStats as $stats)
        @include('partials.dashboard-platform-section', [
          'platform' => $stats['platform'],
          'stats' => $stats,
          'financialYearLabel' => $financialYearLabel,
          'previousFinancialYearLabel' => $previousFinancialYearLabel,
          'financialYearStartDate' => $financialYearStartDate,
        ])
      @empty
        <p class="mt-8 text-sm text-gray-500">No platforms available. Add a platform to see the dashboard.</p>
      @endforelse
    </div>
  </main>
@endsection
