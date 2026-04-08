@extends('layouts.main')

@section('content')
  <main class="flex-1 bg-white">
    <div class="px-12 py-10">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-semibold text-gray-900">Budget</h1>
          <p class="mt-1 text-sm text-gray-500">Financial year runs July — June</p>
        </div>
        <div class="rounded-xl bg-gray-50 px-5 py-3 ring-1 ring-gray-200">
          <p class="text-xs font-medium uppercase tracking-wider text-gray-500">Yearly Budget</p>
          <p class="mt-1 text-xl font-semibold text-gray-900">MUR {{ number_format((float) $yearlyTotal) }}</p>
        </div>
      </div>
      <div class="mt-6 h-px w-full bg-gray-100"></div>

      {{-- Financial year selector --}}
      <div class="mt-6 flex items-center justify-between">
        <div class="inline-flex items-center gap-1 rounded-xl bg-gray-50 p-1 ring-1 ring-gray-200">
          <a href="{{ route('budgets.index', ['fy' => $previousFinancialYearStart]) }}"
             class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-white hover:shadow-sm"
             aria-label="Previous financial year">
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 0 1-.02 1.06L8.832 10l3.938 3.71a.75.75 0 1 1-1.04 1.08l-4.5-4.25a.75.75 0 0 1 0-1.08l4.5-4.25a.75.75 0 0 1 1.06.02Z" clip-rule="evenodd" />
            </svg>
            Prev
          </a>
          <div class="px-4 py-1.5 text-sm font-semibold text-gray-900">
            FY {{ $financialYearLabel }}
            @if($isCurrentFinancialYear)
              <span class="ml-1 inline-flex items-center rounded-full bg-gray-900 px-2 py-0.5 text-[10px] font-medium uppercase text-white">Current</span>
            @endif
          </div>
          <a href="{{ route('budgets.index', ['fy' => $nextFinancialYearStart]) }}"
             class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-white hover:shadow-sm"
             aria-label="Next financial year">
            Next
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" />
            </svg>
          </a>
        </div>

        @unless($isCurrentFinancialYear)
          <a href="{{ route('budgets.index') }}" class="text-sm font-medium text-gray-700 hover:text-gray-900">
            Jump to current FY →
          </a>
        @endunless
      </div>

      @if(session('success'))
        <div class="mt-6 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
          {{ session('success') }}
        </div>
      @endif

      @forelse($platforms as $platform)
        @php
          $platformBudgets = $budgets->get($platform->id) ?? collect();
          $platformYearlyTotal = (float) ($yearlyTotalsByPlatform[$platform->id] ?? 0);
        @endphp
        <div class="mt-10">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">{{ $platform->name }}</h2>
            <p class="text-sm text-gray-500">Yearly Budget: <span class="font-semibold text-gray-900">MUR {{ number_format($platformYearlyTotal) }}</span></p>
          </div>
          <div class="mt-4">
            <table class="min-w-full divide-y divide-gray-200">
              <thead>
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Month</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Monthly Budget</th>
                  <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-500">Salesperson Targets</th>
                  <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-500">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100">
                @foreach($months as $month)
                  @php
                    $key = $month['year'].'-'.$month['month'];
                    $budget = $platformBudgets->get($key);
                    $targetCount = $budget?->salespersonTargets->count() ?? 0;
                  @endphp
                  <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $month['label'] }}</td>
                    <td class="px-4 py-3 text-sm text-gray-600">
                      @if($budget)
                        MUR {{ number_format((float) $budget->amount) }}
                      @else
                        <span class="text-gray-400">—</span>
                      @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">
                      @if($targetCount > 0)
                        {{ $targetCount }} {{ Str::plural('target', $targetCount) }} set
                      @else
                        <span class="text-gray-400">No targets set</span>
                      @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                      <a href="{{ route('budgets.edit', ['platform' => $platform, 'year' => $month['year'], 'month' => $month['month']]) }}" class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                        Set Budget
                      </a>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      @empty
        <p class="mt-8 text-sm text-gray-500">No platforms available. Add a platform before setting budgets.</p>
      @endforelse
    </div>
  </main>
@endsection
