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
      $yearPctClass = $stats['yearlyPercentage'] >= 100 ? 'text-green-600' : ($stats['yearlyPercentage'] >= 75 ? 'text-amber-600' : 'text-gray-900');
    @endphp
    <p class="mt-2 text-2xl font-semibold {{ $yearPctClass }}">{{ number_format($stats['yearlyPercentage'], 1) }}%</p>
    <p class="mt-1 text-xs text-gray-400">Achieved</p>
    <div class="mt-3 h-1.5 w-full overflow-hidden rounded-full bg-gray-100">
      <div class="h-full bg-gray-900" style="width: {{ min(100, $stats['yearlyPercentage']) }}%"></div>
    </div>
  </div>
</div>

{{-- Second row: salesperson, monthly comparison, placement earnings --}}
<div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-4">
  {{-- Salesperson bookings & sales --}}
  <div class="rounded-2xl bg-white p-5 ring-1 ring-gray-200 shadow-sm lg:col-span-1">
    <p class="text-xs font-medium uppercase tracking-wider text-gray-500">Salesperson Performance</p>
    <p class="mt-1 text-xs text-gray-400">FY {{ $financialYearLabel }}</p>
    <div class="mt-4 space-y-3">
      @forelse($stats['salespersonStats'] as $salesperson)
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <p class="truncate text-sm font-medium text-gray-900">
              {{ $salesperson->first_name }} {{ $salesperson->last_name }}
            </p>
            <p class="text-xs text-gray-500">{{ (int) $salesperson->bookings_count }} bookings</p>
          </div>
          <p class="shrink-0 text-sm font-semibold text-gray-900">
            MUR {{ number_format((float) $salesperson->sales_total) }}
          </p>
        </div>
      @empty
        <p class="text-sm text-gray-500">No salespersons yet.</p>
      @endforelse
    </div>
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
          <span class="inline-block h-2.5 w-2.5 rounded-sm bg-gray-900"></span>
          {{ $financialYearLabel }}
        </span>
        <span class="flex items-center gap-1.5">
          <span class="inline-block h-2.5 w-2.5 rounded-sm bg-gray-300"></span>
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
              class="w-1/2 rounded-t bg-gray-900"
              style="height: {{ $currentHeight }}%;"
              title="{{ $monthRow['label'] }} {{ $financialYearLabel }}: MUR {{ number_format($monthRow['current']) }}"
            ></div>
            <div
              class="w-1/2 rounded-t bg-gray-300"
              style="height: {{ $previousHeight }}%;"
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
