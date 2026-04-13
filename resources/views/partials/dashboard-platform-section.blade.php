{{-- Per-platform dashboard section --}}
<h2 class="mt-10 text-xl font-semibold text-gray-900">{{ $platform->name }}</h2>
<div class="mt-4 h-px w-full bg-gray-100"></div>

{{-- KPI cards --}}
<div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
  {{-- Yearly Budget --}}
  <div class="rounded-2xl bg-white p-5 ring-1 ring-gray-200 shadow-sm">
    <p class="text-xs font-medium uppercase tracking-wider text-gray-500">Yearly Budget</p>
    <p class="mt-2 text-2xl font-semibold text-gray-900">MUR {{ number_format($stats['yearlyBudget']) }}</p>
    <p class="mt-1 text-xs text-gray-400">FY {{ $financialYearLabel }}</p>
  </div>

  {{-- Current Month Target --}}
  <div class="rounded-2xl bg-white p-5 ring-1 ring-gray-200 shadow-sm">
    <p class="text-xs font-medium uppercase tracking-wider text-gray-500">{{ now()->format('F') }} Target</p>
    <p class="mt-2 text-2xl font-semibold text-gray-900">MUR {{ number_format($stats['currentMonthBudget']) }}</p>
    <p class="mt-1 text-xs text-gray-400">Monthly budget</p>
  </div>

  {{-- Current Month Sales --}}
  <div class="rounded-2xl bg-white p-5 ring-1 ring-gray-200 shadow-sm">
    <p class="text-xs font-medium uppercase tracking-wider text-gray-500">{{ now()->format('F') }} Sales</p>
    <p class="mt-2 text-2xl font-semibold text-gray-900">MUR {{ number_format($stats['currentMonthSales']) }}</p>
    @php
      $monthPctClass = $stats['currentMonthPercentage'] >= 100 ? 'text-green-600' : ($stats['currentMonthPercentage'] >= 75 ? 'text-amber-600' : 'text-gray-500');
    @endphp
    <p class="mt-1 text-xs font-medium {{ $monthPctClass }}">
      {{ number_format($stats['currentMonthPercentage'], 1) }}% of target
    </p>
  </div>

  {{-- Cumulated Sales since FY start --}}
  <div class="rounded-2xl bg-white p-5 ring-1 ring-gray-200 shadow-sm">
    <p class="text-xs font-medium uppercase tracking-wider text-gray-500">Cumulated Sales</p>
    <p class="mt-2 text-2xl font-semibold text-gray-900">MUR {{ number_format($stats['cumulatedSales']) }}</p>
    <p class="mt-1 text-xs text-gray-400">Since {{ $financialYearStartDate->format('M Y') }}</p>
  </div>

  {{-- Yearly Target Achieved % --}}
  <div class="rounded-2xl bg-white p-5 ring-1 ring-gray-200 shadow-sm">
    <p class="text-xs font-medium uppercase tracking-wider text-gray-500">Yearly Target</p>
    @php
      $yearlyState = $stats['yearlyTargetState'] ?? 'neutral';
      $yearPctClass = match ($yearlyState) {
          'realisable' => 'text-green-600',
          'below_average' => 'text-amber-600',
          'unrealistic' => 'text-red-600',
          default => 'text-gray-900',
      };
      $yearBarClass = match ($yearlyState) {
          'realisable' => 'bg-green-600',
          'below_average' => 'bg-amber-600',
          'unrealistic' => 'bg-red-600',
          default => 'bg-gray-900',
      };
    @endphp
    <p class="mt-2 text-2xl font-semibold {{ $yearPctClass }}">{{ number_format($stats['yearlyPercentage'], 1) }}%</p>
    <p class="mt-1 text-xs {{ $yearPctClass }}">Achieved</p>
    <div class="mt-3 h-1.5 w-full overflow-hidden rounded-full bg-gray-100">
      <div class="h-full {{ $yearBarClass }}" style="width: {{ min(100, $stats['yearlyPercentage']) }}%"></div>
    </div>
  </div>
</div>

{{-- Second row: salesperson, monthly comparison, placement earnings --}}
<div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-4">
  {{-- Salesperson reservations & sales --}}
  <div class="rounded-2xl bg-white p-5 ring-1 ring-gray-200 shadow-sm lg:col-span-1"
    x-data="{ showAllPerformance: false, showTargets: false }">
    <p class="text-xs font-medium uppercase tracking-wider text-gray-500">Salesperson Performance</p>
    <p class="mt-1 text-xs text-gray-400">FY {{ $financialYearLabel }}</p>
    <div class="mt-4 space-y-3">
      @forelse($stats['salespersonStats']->take(4) as $salesperson)
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <p class="truncate text-sm font-medium text-gray-900">
              {{ $salesperson->first_name }} {{ $salesperson->last_name }}
            </p>
            <p class="text-xs text-gray-500">{{ (int) $salesperson->reservations_count }} reservations</p>
          </div>
          <p class="shrink-0 text-sm font-semibold text-gray-900">
            MUR {{ number_format((float) $salesperson->sales_total) }}
          </p>
        </div>
      @empty
        <p class="text-sm text-gray-500">No salespersons yet.</p>
      @endforelse
    </div>

    @if($stats['salespersonStats']->count() > 0)
      <div class="mt-4 flex gap-2">
        <button type="button" @click="showAllPerformance = true"
          class="w-full rounded-lg border border-gray-200 px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50">
          View All
        </button>

        @can('view-targets')
          <button type="button" @click="showTargets = true"
            class="w-full rounded-lg border border-gray-200 px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50">
            Monthly Targets
          </button>
        @endcan
      </div>
    @endif

    {{-- View All Performance Modal --}}
    <div x-show="showAllPerformance" x-cloak @keydown.escape.window="showAllPerformance = false"
      class="fixed inset-0 z-50 flex items-start justify-center px-4 pt-24" style="display: none;">
      <div class="fixed inset-0 bg-gray-900/40" @click="showAllPerformance = false"></div>

      <div x-show="showAllPerformance"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        class="relative w-full max-w-lg rounded-2xl bg-white shadow-xl ring-1 ring-gray-200">
        <div class="flex items-start justify-between border-b border-gray-100 px-6 py-4">
          <div>
            <h2 class="text-base font-semibold text-gray-900">Salesperson Performance</h2>
            <p class="mt-1 text-xs text-gray-500">{{ $platform->name }} &middot; FY {{ $financialYearLabel }}</p>
          </div>
          <button type="button" @click="showAllPerformance = false" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
              <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
            </svg>
          </button>
        </div>

        <div class="max-h-[60vh] space-y-3 overflow-y-auto px-6 py-5">
          @foreach($stats['salespersonStats'] as $salesperson)
            <div class="flex items-start justify-between gap-3">
              <div class="min-w-0">
                <p class="truncate text-sm font-medium text-gray-900">
                  {{ $salesperson->first_name }} {{ $salesperson->last_name }}
                </p>
                <p class="text-xs text-gray-500">{{ (int) $salesperson->reservations_count }} reservations</p>
              </div>
              <p class="shrink-0 text-sm font-semibold text-gray-900">
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
        class="fixed inset-0 z-50 flex items-start justify-center px-4 pt-16" style="display: none;">
        <div class="fixed inset-0 bg-gray-900/40" @click="showTargets = false"></div>

        <div x-show="showTargets"
          x-transition:enter="transition ease-out duration-150"
          x-transition:enter-start="opacity-0 -translate-y-2"
          x-transition:enter-end="opacity-100 translate-y-0"
          class="relative w-full max-w-2xl rounded-2xl bg-white shadow-xl ring-1 ring-gray-200">
          <div class="flex items-start justify-between border-b border-gray-100 px-6 py-4">
            <div>
              <h2 class="text-base font-semibold text-gray-900">Monthly Targets</h2>
              <p class="mt-1 text-xs text-gray-500">{{ $platform->name }} &middot; FY {{ $financialYearLabel }}</p>
            </div>
            <div class="flex items-center gap-2">
              <a href="{{ route('sales-performance.export', ['platform_id' => $platform->id, 'format' => 'csv']) }}"
                class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                Export CSV
              </a>
              <a href="{{ route('sales-performance.export', ['platform_id' => $platform->id, 'format' => 'pdf']) }}"
                class="rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                Export PDF
              </a>
              <button type="button" @click="showTargets = false" class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600">
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                  <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                </svg>
              </button>
            </div>
          </div>

          <div class="max-h-[60vh] overflow-y-auto px-6 py-5">
            <table class="w-full text-left text-sm">
              <thead>
                <tr class="border-b border-gray-100">
                  <th class="pb-3 text-xs font-medium uppercase tracking-wider text-gray-500">Salesperson</th>
                  <th class="pb-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Target</th>
                  <th class="pb-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Sales</th>
                  <th class="pb-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Achievement</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-50">
                @foreach($stats['salespersonTargets'] as $entry)
                  @php
                    $pctClass = $entry['percentage'] >= 100 ? 'text-green-600' : ($entry['percentage'] >= 75 ? 'text-amber-600' : 'text-gray-500');
                  @endphp
                  <tr>
                    <td class="py-3 font-medium text-gray-900">
                      {{ $entry['salesperson']->first_name }} {{ $entry['salesperson']->last_name }}
                    </td>
                    <td class="py-3 text-right text-gray-700">MUR {{ number_format($entry['target']) }}</td>
                    <td class="py-3 text-right text-gray-700">MUR {{ number_format($entry['sales']) }}</td>
                    <td class="py-3 text-right font-semibold {{ $pctClass }}">
                      {{ number_format($entry['percentage'], 1) }}%
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>

            @if(empty($stats['salespersonTargets']))
              <p class="py-4 text-center text-sm text-gray-500">No targets set for this financial year.</p>
            @endif
          </div>
        </div>
      </div>
    @endcan
  </div>

  {{-- Monthly sales comparison bar chart --}}
  <div class="rounded-2xl bg-white p-5 ring-1 ring-gray-200 shadow-sm lg:col-span-2">
    <div class="flex items-start justify-between gap-3">
      <div>
        <p class="text-xs font-medium uppercase tracking-wider text-gray-500">Monthly Sales Comparison</p>
        <p class="mt-1 text-xs text-gray-400">FY {{ $financialYearLabel }} vs FY {{ $previousFinancialYearLabel }}</p>
      </div>
      <div class="flex items-center gap-3 text-xs text-gray-500">
        <span class="flex items-center gap-1.5">
          <span class="inline-block h-2.5 w-2.5 rounded-sm" style="background-color: {{ $stats['monthlySalesCurrentColor'] }};"></span>
          {{ $financialYearLabel }}
        </span>
        <span class="flex items-center gap-1.5">
          <span class="inline-block h-2.5 w-2.5 rounded-sm" style="background-color: {{ $stats['monthlySalesPreviousColor'] }};"></span>
          {{ $previousFinancialYearLabel }}
        </span>
      </div>
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
        <p class="text-center text-[10px] font-medium text-gray-500">{{ $monthRow['label'] }}</p>
      @endforeach
    </div>
  </div>

  {{-- Placement earnings: Web vs Social Media --}}
  <div class="rounded-2xl bg-white p-5 ring-1 ring-gray-200 shadow-sm lg:col-span-1">
    <p class="text-xs font-medium uppercase tracking-wider text-gray-500">Placement Earnings</p>
    <p class="mt-1 text-xs text-gray-400">FY {{ $financialYearLabel }}</p>
    @php
      $webEarnings = (float) ($stats['placementEarnings'][\App\PlacementType::Web->value] ?? 0);
      $socialEarnings = (float) ($stats['placementEarnings'][\App\PlacementType::SocialMedia->value] ?? 0);
      $totalEarnings = $webEarnings + $socialEarnings;
      $webShare = $totalEarnings > 0 ? ($webEarnings / $totalEarnings) * 100 : 0;
      $socialShare = $totalEarnings > 0 ? ($socialEarnings / $totalEarnings) * 100 : 0;
    @endphp
    <div class="mt-4 space-y-4">
      <div>
        <div class="flex items-center justify-between gap-3">
          <p class="text-sm font-medium text-gray-900">Web</p>
          <p class="text-xs font-medium text-gray-500">{{ number_format($webShare, 1) }}%</p>
        </div>
        <p class="mt-1 text-sm font-semibold text-gray-900">MUR {{ number_format($webEarnings) }}</p>
        <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-gray-100">
          <div class="h-full bg-gray-900" style="width: {{ $webShare }}%;"></div>
        </div>
      </div>
      <div>
        <div class="flex items-center justify-between gap-3">
          <p class="text-sm font-medium text-gray-900">Social Media</p>
          <p class="text-xs font-medium text-gray-500">{{ number_format($socialShare, 1) }}%</p>
        </div>
        <p class="mt-1 text-sm font-semibold text-gray-900">MUR {{ number_format($socialEarnings) }}</p>
        <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-gray-100">
          <div class="h-full bg-gray-500" style="width: {{ $socialShare }}%;"></div>
        </div>
      </div>
    </div>
  </div>
</div>
