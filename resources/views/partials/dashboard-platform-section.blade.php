{{-- Per-platform dashboard section --}}
<h2 class="mt-10 text-xl font-medium text-ls-text">{{ $platform->name }}</h2>
<div class="mt-4 h-px w-full bg-ls-border"></div>

{{-- KPI cards --}}
<div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
  {{-- Yearly Budget --}}
  <div class="ls-kpi">
    <div class="lbl">Yearly Budget</div>
    <div class="val">MUR {{ number_format($stats['yearlyBudget']) }}</div>
    <div class="sub">FY {{ $financialYearLabel }}</div>
  </div>

  {{-- Current Month Target --}}
  <div class="ls-kpi">
    <div class="lbl">{{ now()->format('F') }} Target</div>
    <div class="val">MUR {{ number_format($stats['currentMonthBudget']) }}</div>
    <div class="sub">Monthly budget</div>
  </div>

  {{-- Current Month Sales --}}
  <div class="ls-kpi">
    <div class="lbl">{{ now()->format('F') }} Sales</div>
    <div class="val">MUR {{ number_format($stats['currentMonthSales']) }}</div>
    <div class="sub">{{ number_format($stats['currentMonthPercentage'], 1) }}% of target</div>
  </div>

  {{-- Cumulated Sales since FY start --}}
  <div class="ls-kpi">
    <div class="lbl">Cumulated Sales</div>
    <div class="val">MUR {{ number_format($stats['cumulatedSales']) }}</div>
    <div class="sub">Since {{ $financialYearStartDate->format('M Y') }}</div>
  </div>

  {{-- Yearly Target Achieved % --}}
  <div class="ls-kpi">
    <div class="lbl">Yearly Target</div>
    @php
      $yearlyState = $stats['yearlyTargetState'] ?? 'neutral';
      $yearPctClass = match ($yearlyState) {
          'realisable' => 'text-green-600',
          'below_average' => 'text-amber-600',
          'unrealistic' => 'text-red-600',
          default => 'text-ls-text',
      };
      $yearBarClass = match ($yearlyState) {
          'realisable' => 'bg-green-600',
          'below_average' => 'bg-amber-600',
          'unrealistic' => 'bg-red-600',
          default => 'bg-ls-text',
      };
    @endphp
    <div class="val {{ $yearPctClass }}">{{ number_format($stats['yearlyPercentage'], 1) }}%</div>
    <div class="sub">FY {{ $financialYearLabel }}</div>
    <div class="mt-3 h-1.5 w-full overflow-hidden rounded-full bg-ls-surface-muted">
      <div class="h-full {{ $yearBarClass }}" style="width: {{ min(100, $stats['yearlyPercentage']) }}%"></div>
    </div>
  </div>
</div>

{{-- Monthly Sales vs Budget table --}}
<x-ls.card class="mt-6">
  <p class="text-[10px] font-medium uppercase tracking-wider text-ls-text-muted">Monthly Sales vs Budget</p>
  <p class="mt-1 text-xs text-ls-text-muted">FY {{ $financialYearLabel }}</p>

  <div class="mt-4 overflow-x-auto">
    <table class="w-full text-left text-xs">
      <thead>
        <tr class="border-b border-ls-border">
          <th class="pb-2 pr-4 font-medium uppercase tracking-wider text-ls-text-muted">Month</th>
          @foreach($stats['monthlySalesVsBudget'] as $row)
            <th class="pb-2 text-right font-medium uppercase tracking-wider text-ls-text-muted">{{ Str::before($row['label'], ' ') }}</th>
          @endforeach
          <th class="pb-2 pl-4 text-right font-medium uppercase tracking-wider text-ls-text">Total</th>
        </tr>
      </thead>
      <tbody>
        @php
          $totalBudget = 0;
          $totalSales = 0;
          foreach ($stats['monthlySalesVsBudget'] as $row) {
              $totalBudget += $row['budget'];
              $totalSales += $row['sales'];
          }
        @endphp
        <tr class="border-b border-ls-border">
          <td class="py-2 pr-4 font-medium text-ls-text">Budget</td>
          @foreach($stats['monthlySalesVsBudget'] as $row)
            <td class="py-2 text-right text-ls-text">{{ number_format($row['budget']) }}</td>
          @endforeach
          <td class="py-2 pl-4 text-right font-medium text-ls-text">{{ number_format($totalBudget) }}</td>
        </tr>
        <tr class="border-b border-ls-border">
          <td class="py-2 pr-4 font-medium text-ls-text">Sales</td>
          @foreach($stats['monthlySalesVsBudget'] as $row)
            <td class="py-2 text-right text-ls-text">{{ number_format($row['sales']) }}</td>
          @endforeach
          <td class="py-2 pl-4 text-right font-medium text-ls-text">{{ number_format($totalSales) }}</td>
        </tr>
        <tr>
          <td class="py-2 pr-4 font-medium text-ls-text">Variance</td>
          @foreach($stats['monthlySalesVsBudget'] as $row)
            @php
              $variance = $row['sales'] - $row['budget'];
              $varClass = $variance > 0 ? 'text-green-600' : ($variance < 0 ? 'text-red-600' : 'text-ls-text-muted');
            @endphp
            <td class="py-2 text-right font-medium {{ $varClass }}">{{ ($variance >= 0 ? '+' : '') . number_format($variance) }}</td>
          @endforeach
          @php
            $totalVariance = $totalSales - $totalBudget;
            $totalVarClass = $totalVariance > 0 ? 'text-green-600' : ($totalVariance < 0 ? 'text-red-600' : 'text-ls-text-muted');
          @endphp
          <td class="py-2 pl-4 text-right font-medium {{ $totalVarClass }}">{{ ($totalVariance >= 0 ? '+' : '') . number_format($totalVariance) }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</x-ls.card>

{{-- Second row: salesperson, monthly comparison, placement earnings --}}
<div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-4">
  {{-- Salesperson reservations & sales --}}
  <x-ls.card class="lg:col-span-1" x-data="{ showAllPerformance: false, showTargets: false }">
    <p class="text-[10px] font-medium uppercase tracking-wider text-ls-text-muted">Salesperson Performance</p>
    <p class="mt-1 text-xs text-ls-text-muted">FY {{ $financialYearLabel }}</p>
    <div class="mt-4 space-y-3">
      @forelse($stats['salespersonStats']->take(4) as $salesperson)
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <p class="truncate text-sm font-medium text-ls-text">
              {{ $salesperson->first_name }} {{ $salesperson->last_name }}
            </p>
            <p class="text-xs text-ls-text-muted">{{ (int) $salesperson->reservations_count }} reservations</p>
          </div>
          <p class="shrink-0 text-sm font-medium text-ls-text">
            MUR {{ number_format((float) $salesperson->sales_total) }}
          </p>
        </div>
      @empty
        <p class="text-sm text-ls-text-muted">No salespersons yet.</p>
      @endforelse
    </div>

    @if($stats['salespersonStats']->count() > 0)
      <div class="mt-4 flex gap-2">
        <button type="button" @click="showAllPerformance = true"
          class="ls-btn ls-btn-outline ls-btn-sm w-full">
          View All
        </button>

        @can('view-targets')
          <button type="button" @click="showTargets = true"
            class="ls-btn ls-btn-outline ls-btn-sm w-full">
            Monthly Targets
          </button>
        @endcan
      </div>
    @endif

    {{-- View All Performance Modal --}}
    <div x-show="showAllPerformance" x-cloak @keydown.escape.window="showAllPerformance = false"
      class="fixed inset-0 z-50 flex items-start justify-center px-4 pt-24" style="display: none;">
      <div class="fixed inset-0" style="background: var(--ls-overlay-scrim);" @click="showAllPerformance = false"></div>

      <div x-show="showAllPerformance"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        class="relative w-full max-w-lg rounded-2xl bg-ls-surface shadow-xl ring-1 ring-ls-border-strong">
        <div class="flex items-start justify-between border-b border-ls-border px-6 py-4">
          <div>
            <h2 class="text-base font-medium text-ls-text">Salesperson Performance</h2>
            <p class="mt-1 text-xs text-ls-text-muted">{{ $platform->name }} &middot; FY {{ $financialYearLabel }}</p>
          </div>
          <button type="button" @click="showAllPerformance = false" class="rounded-lg p-1 text-ls-text-muted hover:bg-ls-surface-muted hover:text-ls-text">
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
            </svg>
          </button>
        </div>

        <div class="max-h-[60vh] space-y-3 overflow-y-auto px-6 py-5">
          @foreach($stats['salespersonStats'] as $salesperson)
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0">
                <p class="truncate text-sm font-medium text-ls-text">
                  {{ $salesperson->first_name }} {{ $salesperson->last_name }}
                </p>
                <p class="text-xs text-ls-text-muted">{{ (int) $salesperson->reservations_count }} reservations</p>
              </div>
              <p class="shrink-0 text-sm font-medium text-ls-text">
                MUR {{ number_format((float) $salesperson->sales_total) }}
              </p>
            </div>
          @endforeach
        </div>
      </div>
    </div>

    {{-- Monthly Targets Modal --}}
    @can('view-targets')
      <div x-show="showTargets" x-cloak @keydown.escape.window="showTargets = false"
        class="fixed inset-0 z-50 flex items-start justify-center px-4 pt-10" style="display: none;">
        <div class="fixed inset-0" style="background: var(--ls-overlay-scrim);" @click="showTargets = false"></div>

        <div x-show="showTargets"
          x-transition:enter="transition ease-out duration-150"
          x-transition:enter-start="opacity-0 -translate-y-2"
          x-transition:enter-end="opacity-100 translate-y-0"
          class="relative w-full max-w-5xl rounded-2xl bg-ls-surface shadow-xl ring-1 ring-ls-border-strong">
          <div class="flex items-start justify-between border-b border-ls-border px-6 py-4">
            <div>
              <h2 class="text-base font-medium text-ls-text">Monthly Targets</h2>
              <p class="mt-1 text-xs text-ls-text-muted">{{ $platform->name }} &middot; FY {{ $financialYearLabel }}</p>
            </div>
            <div class="flex items-center gap-2">
              <x-ls.button :href="route('sales-performance.export', ['platform_id' => $platform->id, 'format' => 'csv'])" variant="outline" size="sm">
                Export CSV
              </x-ls.button>
              <x-ls.button :href="route('sales-performance.export', ['platform_id' => $platform->id, 'format' => 'pdf'])" variant="outline" size="sm">
                Export PDF
              </x-ls.button>
              <button type="button" @click="showTargets = false" class="rounded-lg p-1 text-ls-text-muted hover:bg-ls-surface-muted hover:text-ls-text">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                  <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                </svg>
              </button>
            </div>
          </div>

          <div class="max-h-[75vh] overflow-y-auto px-6 py-5">
            @php $targetMonths = $stats['salespersonTargets']['months']; @endphp

            @forelse($stats['salespersonTargets']['salespersons'] as $entry)
              @php
                $pctClass = $entry['totals']['percentage'] >= 100 ? 'text-green-600' : ($entry['totals']['percentage'] >= 75 ? 'text-amber-600' : 'text-ls-text-muted');
              @endphp
              <div class="{{ ! $loop->first ? 'mt-6 border-t border-ls-border pt-6' : '' }}">
                <div class="flex items-baseline justify-between gap-3">
                  <h3 class="text-sm font-medium text-ls-text">
                    {{ $entry['salesperson']->first_name }} {{ $entry['salesperson']->last_name }}
                  </h3>
                  <span class="text-xs font-medium {{ $pctClass }}">
                    {{ number_format($entry['totals']['percentage'], 1) }}% achievement
                  </span>
                </div>

                <div class="mt-3 overflow-x-auto">
                  <table class="w-full text-left text-xs">
                    <thead>
                      <tr class="border-b border-ls-border">
                        <th class="pb-2 pr-3 font-medium uppercase tracking-wider text-ls-text-muted">Month</th>
                        <th class="pb-2 pr-3 text-right font-medium uppercase tracking-wider text-ls-text-muted">Target</th>
                        <th class="pb-2 pr-3 text-right font-medium uppercase tracking-wider text-ls-text-muted">Sales</th>
                        <th class="pb-2 text-right font-medium uppercase tracking-wider text-ls-text-muted">Reservations</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($targetMonths as $i => $month)
                        <tr class="border-b border-ls-border/50">
                          <td class="py-2 pr-3 text-ls-text">{{ $month['label'] }}</td>
                          <td class="py-2 pr-3 text-right text-ls-text">MUR {{ number_format($entry['months'][$i]['target']) }}</td>
                          <td class="py-2 pr-3 text-right text-ls-text">MUR {{ number_format($entry['months'][$i]['sales']) }}</td>
                          <td class="py-2 text-right text-ls-text">{{ $entry['months'][$i]['reservations'] }}</td>
                        </tr>
                      @endforeach
                      <tr class="border-t border-ls-border-strong bg-ls-surface-muted font-medium">
                        <td class="py-2 pr-3 text-ls-text">FY Total</td>
                        <td class="py-2 pr-3 text-right text-ls-text">MUR {{ number_format($entry['totals']['target']) }}</td>
                        <td class="py-2 pr-3 text-right text-ls-text">MUR {{ number_format($entry['totals']['sales']) }}</td>
                        <td class="py-2 text-right text-ls-text">{{ $entry['totals']['reservations'] }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            @empty
              <p class="py-4 text-center text-sm text-ls-text-muted">No salespersons found.</p>
            @endforelse
          </div>
        </div>
      </div>
    @endcan
  </x-ls.card>

  {{-- Monthly sales comparison bar chart --}}
  <x-ls.card class="lg:col-span-2">
    <div>
      <p class="text-[10px] font-medium uppercase tracking-wider text-ls-text-muted">Monthly Sales Comparison</p>
      <p class="mt-1 text-xs text-ls-text-muted">
        <span class="inline-flex items-center gap-1.5">
          <span class="inline-block h-2 w-2 rounded-sm" style="background-color: {{ $stats['monthlySalesCurrentColor'] }};"></span>
          FY {{ $financialYearLabel }}
        </span>
        <span class="mx-1">vs</span>
        <span class="inline-flex items-center gap-1.5">
          <span class="inline-block h-2 w-2 rounded-sm" style="background-color: {{ $stats['monthlySalesPreviousColor'] }};"></span>
          FY {{ $previousFinancialYearLabel }}
        </span>
      </p>
    </div>

    <div class="mt-6 grid grid-cols-12 items-end gap-2" style="height: 180px;">
      @foreach($stats['monthlySalesComparison'] as $monthRow)
        @php
          $currentHeight = $stats['monthlySalesMax'] > 0 ? ($monthRow['current'] / $stats['monthlySalesMax']) * 100 : 0;
          $previousHeight = $stats['monthlySalesMax'] > 0 ? ($monthRow['previous'] / $stats['monthlySalesMax']) * 100 : 0;
        @endphp
        <div class="flex h-full flex-col items-center justify-end">
          <div class="flex h-full w-full items-end justify-center gap-0.5">
            <div
              class="w-1/2 rounded-t"
              style="height: {{ $currentHeight }}%; background-color: {{ $stats['monthlySalesCurrentColor'] }};"
              title="{{ $monthRow['label'] }} {{ $financialYearLabel }}: MUR {{ number_format($monthRow['current']) }}"
            ></div>
            <div
              class="w-1/2 rounded-t"
              style="height: {{ $previousHeight }}%; background-color: {{ $stats['monthlySalesPreviousColor'] }};"
              title="{{ $monthRow['label'] }} {{ $previousFinancialYearLabel }}: MUR {{ number_format($monthRow['previous']) }}"
            ></div>
          </div>
        </div>
      @endforeach
    </div>
    <div class="mt-2 grid grid-cols-12 gap-2">
      @foreach($stats['monthlySalesComparison'] as $monthRow)
        <p class="text-center text-[10px] font-medium text-ls-text-muted">{{ $monthRow['label'] }}</p>
      @endforeach
    </div>
  </x-ls.card>

  {{-- Placement earnings: Web vs Social Media --}}
  <x-ls.card class="lg:col-span-1">
    <p class="text-[10px] font-medium uppercase tracking-wider text-ls-text-muted">Sales by Placement</p>
    <p class="mt-1 text-xs text-ls-text-muted">FY {{ $financialYearLabel }}</p>
    @php
      $webEarnings = (float) ($stats['placementEarnings'][\App\PlacementType::Web->value] ?? 0);
      $socialEarnings = (float) ($stats['placementEarnings'][\App\PlacementType::SocialMedia->value] ?? 0);
      $programmaticEarnings = (float) ($stats['placementEarnings'][\App\PlacementType::Programmatic->value] ?? 0);
      $totalEarnings = $webEarnings + $socialEarnings + $programmaticEarnings;
      $webShare = $totalEarnings > 0 ? ($webEarnings / $totalEarnings) * 100 : 0;
      $socialShare = $totalEarnings > 0 ? ($socialEarnings / $totalEarnings) * 100 : 0;
      $programmaticShare = $totalEarnings > 0 ? ($programmaticEarnings / $totalEarnings) * 100 : 0;
    @endphp
    <div class="mt-4 space-y-4">
      <div>
        <div class="flex items-center justify-between gap-3">
          <p class="text-sm font-medium text-ls-text">Web</p>
          <p class="text-xs font-medium text-ls-text-muted">{{ number_format($webShare, 1) }}%</p>
        </div>
        <p class="mt-1 text-sm font-medium text-ls-text">MUR {{ number_format($webEarnings) }}</p>
        <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-ls-surface-muted">
          <div class="h-full bg-ls-deep" style="width: {{ $webShare }}%;"></div>
        </div>
      </div>
      <div>
        <div class="flex items-center justify-between gap-3">
          <p class="text-sm font-medium text-ls-text">Social Media</p>
          <p class="text-xs font-medium text-ls-text-muted">{{ number_format($socialShare, 1) }}%</p>
        </div>
        <p class="mt-1 text-sm font-medium text-ls-text">MUR {{ number_format($socialEarnings) }}</p>
        <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-ls-surface-muted">
          <div class="h-full bg-ls-deep" style="width: {{ $socialShare }}%;"></div>
        </div>
      </div>
      <div>
        <div class="flex items-center justify-between gap-3">
          <p class="text-sm font-medium text-ls-text">Programmatic</p>
          <p class="text-xs font-medium text-ls-text-muted">{{ number_format($programmaticShare, 1) }}%</p>
        </div>
        <p class="mt-1 text-sm font-medium text-ls-text">MUR {{ number_format($programmaticEarnings) }}</p>
        <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-ls-surface-muted">
          <div class="h-full bg-ls-deep" style="width: {{ $programmaticShare }}%;"></div>
        </div>
      </div>
    </div>
  </x-ls.card>
</div>
