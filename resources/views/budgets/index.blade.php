@extends('layouts.main')

@section('title', 'Budget • Digital Bookings')

@section('content')
  <x-ls.page>
    <x-ls.page-header title="Budget" subtitle="Financial year runs July — June">
      <x-slot name="actions">
        <div class="rounded-xl bg-ls-surface-muted px-5 py-3 ring-1 ring-ls-border-strong">
          <p class="text-xs font-medium uppercase tracking-wider text-ls-text-muted">Yearly Budget</p>
          <p class="mt-1 text-xl font-medium text-ls-text">MUR {{ number_format((float) $yearlyTotal) }}</p>
        </div>
      </x-slot>
    </x-ls.page-header>

    {{-- Financial year selector --}}
    <div class="mt-6 flex items-center justify-between">
      <div class="inline-flex items-center gap-1 rounded-xl bg-ls-surface-muted p-1 ring-1 ring-ls-border-strong">
        <a href="{{ route('budgets.index', ['fy' => $previousFinancialYearStart]) }}"
           class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-sm font-medium text-ls-text hover:bg-ls-surface hover:shadow-sm"
           aria-label="Previous financial year">
          <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 0 1-.02 1.06L8.832 10l3.938 3.71a.75.75 0 1 1-1.04 1.08l-4.5-4.25a.75.75 0 0 1 0-1.08l4.5-4.25a.75.75 0 0 1 1.06.02Z" clip-rule="evenodd" />
          </svg>
          Prev
        </a>
        <div class="px-4 py-1.5 text-sm font-medium text-ls-text">
          FY {{ $financialYearLabel }}
          @if($isCurrentFinancialYear)
            <span class="ml-1 inline-flex items-center rounded-full bg-ls-deep px-2 py-0.5 text-[10px] font-medium uppercase text-white">Current</span>
          @endif
        </div>
        <a href="{{ route('budgets.index', ['fy' => $nextFinancialYearStart]) }}"
           class="inline-flex items-center gap-1 rounded-lg px-3 py-1.5 text-sm font-medium text-ls-text hover:bg-ls-surface hover:shadow-sm"
           aria-label="Next financial year">
          Next
          <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 0 1 .02-1.06L11.168 10 7.23 6.29a.75.75 0 1 1 1.04-1.08l4.5 4.25a.75.75 0 0 1 0 1.08l-4.5 4.25a.75.75 0 0 1-1.06-.02Z" clip-rule="evenodd" />
          </svg>
        </a>
      </div>

      @unless($isCurrentFinancialYear)
        <a href="{{ route('budgets.index') }}" class="text-sm font-medium text-ls-text hover:text-ls-deep">
          Jump to current FY →
        </a>
      @endunless
    </div>

    <div class="mt-6">
      <x-ls.flash />
    </div>

    @forelse($platforms as $platform)
      @php
        $platformBudgets = $budgets->get($platform->id) ?? collect();
        $platformYearlyTotal = (float) ($yearlyTotalsByPlatform[$platform->id] ?? 0);
      @endphp
      <div class="mt-10">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-medium text-ls-text">{{ $platform->name }}</h2>
          <p class="text-sm text-ls-text-muted">Yearly Budget: <span class="font-medium text-ls-text">MUR {{ number_format($platformYearlyTotal) }}</span></p>
        </div>
        <div class="mt-4">
          <table class="ls-table">
            <thead>
              <tr>
                <th>Month</th>
                <th>Monthly Budget</th>
                <th>Salesperson Targets</th>
                <th class="text-right">Actions</th>
              </tr>
            </thead>
            <tbody>
              @foreach($months as $month)
                @php
                  $key = $month['year'].'-'.$month['month'];
                  $budget = $platformBudgets->get($key);
                  $targetCount = $budget?->salespersonTargets->count() ?? 0;
                @endphp
                <tr>
                  <td class="font-medium text-ls-text">{{ $month['label'] }}</td>
                  <td class="text-ls-text-muted">
                    @if($budget)
                      MUR {{ number_format((float) $budget->amount) }}
                    @else
                      <span class="text-ls-text-soft">—</span>
                    @endif
                  </td>
                  <td class="text-ls-text-muted">
                    @if($targetCount > 0)
                      {{ $targetCount }} {{ Str::plural('target', $targetCount) }} set
                    @else
                      <span class="text-ls-text-soft">No targets set</span>
                    @endif
                  </td>
                  <td class="text-right">
                    <x-ls.button :href="route('budgets.edit', ['platform' => $platform, 'year' => $month['year'], 'month' => $month['month']])" variant="outline" size="sm">
                      Set Budget
                    </x-ls.button>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    @empty
      <p class="mt-8 text-sm text-ls-text-muted">No platforms available. Add a platform before setting budgets.</p>
    @endforelse
  </x-ls.page>
@endsection
