<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Platform;
use App\Models\Reservation;
use App\Models\Salesperson;
use App\PlacementType;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class HomeController extends Controller
{
    /**
     * Hex colours for each platform's monthly sales comparison chart, keyed by
     * platform name. Platforms without an entry fall back to neutral grays.
     *
     * @var array<string, array{current: string, previous: string}>
     */
    private const MONTHLY_SALES_CHART_COLORS = [
        'lexpress.mu' => ['current' => '#5e8ef4', 'previous' => '#b0e2f0'],
        '5plus.mu' => ['current' => '#c84670', 'previous' => '#ffbb55'],
    ];

    private const MONTHLY_SALES_CHART_DEFAULT_COLORS = [
        'current' => '#111827',
        'previous' => '#d1d5db',
    ];

    /**
     * Show the dashboard with per-platform budget and sales KPIs.
     */
    public function index(): View
    {
        $now = Carbon::now();
        $financialYearStart = Budget::financialYearStartYear($now);
        $financialYearStartDate = Carbon::create($financialYearStart, Budget::FINANCIAL_YEAR_START_MONTH, 1)->startOfDay();
        $financialYearEndDate = $financialYearStartDate->copy()->addYear()->subDay()->endOfDay();
        $previousFinancialYearStartDate = $financialYearStartDate->copy()->subYear();
        $previousFinancialYearEndDate = $financialYearStartDate->copy()->subDay()->endOfDay();
        $financialYearLabel = Budget::financialYearLabel($financialYearStart);
        $previousFinancialYearLabel = Budget::financialYearLabel($financialYearStart - 1);

        $platforms = Platform::query()->orderBy('id')->get();

        $platformStats = $platforms->map(fn (Platform $platform) => $this->statsForPlatform(
            $platform,
            $now,
            $financialYearStart,
            $financialYearStartDate,
            $financialYearEndDate,
            $previousFinancialYearStartDate,
            $previousFinancialYearEndDate,
        ))->all();

        return view('home', compact(
            'financialYearLabel',
            'previousFinancialYearLabel',
            'financialYearStartDate',
            'platformStats',
        ));
    }

    /**
     * Compute all dashboard metrics for a single platform.
     *
     * @return array<string, mixed>
     */
    private function statsForPlatform(
        Platform $platform,
        Carbon $now,
        int $financialYearStart,
        Carbon $fyStart,
        Carbon $fyEnd,
        Carbon $prevFyStart,
        Carbon $prevFyEnd,
    ): array {
        $yearlyBudget = (float) Budget::forFinancialYear($financialYearStart)
            ->where('platform_id', $platform->id)
            ->sum('amount');

        $currentMonthBudget = (float) Budget::query()
            ->where('platform_id', $platform->id)
            ->where('year', $now->year)
            ->where('month', $now->month)
            ->value('amount');

        $currentMonthSales = (float) Reservation::query()
            ->where('platform_id', $platform->id)
            ->whereBetween('created_at', [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()])
            ->sum('gross_amount');

        $currentMonthPercentage = $currentMonthBudget > 0
            ? ($currentMonthSales / $currentMonthBudget) * 100
            : 0;

        $cumulatedSales = (float) Reservation::query()
            ->where('platform_id', $platform->id)
            ->whereBetween('created_at', [$fyStart, $now])
            ->sum('gross_amount');

        $yearlyPercentage = $yearlyBudget > 0
            ? ($cumulatedSales / $yearlyBudget) * 100
            : 0;

        $expectedYearlyPercentage = $this->expectedYearlyPercentage($fyStart, $fyEnd, $now);
        $yearlyTargetState = $this->yearlyTargetState($yearlyBudget, $yearlyPercentage, $expectedYearlyPercentage);

        $salespersonStats = $this->salespersonStats($platform, $fyStart, $fyEnd);

        $monthlySalesComparison = $this->monthlySalesComparison(
            $platform,
            $financialYearStart,
            $fyStart,
            $fyEnd,
            $prevFyStart,
            $prevFyEnd,
        );

        $monthlySalesMax = 0;
        foreach ($monthlySalesComparison as $row) {
            $monthlySalesMax = max($monthlySalesMax, $row['current'], $row['previous']);
        }

        $placementEarnings = $this->placementEarnings($platform, $fyStart, $fyEnd);

        $monthlySalesColors = self::MONTHLY_SALES_CHART_COLORS[$platform->name]
            ?? self::MONTHLY_SALES_CHART_DEFAULT_COLORS;

        return [
            'platform' => $platform,
            'yearlyBudget' => $yearlyBudget,
            'currentMonthBudget' => $currentMonthBudget,
            'currentMonthSales' => $currentMonthSales,
            'currentMonthPercentage' => $currentMonthPercentage,
            'cumulatedSales' => $cumulatedSales,
            'yearlyPercentage' => $yearlyPercentage,
            'yearlyTargetState' => $yearlyTargetState,
            'salespersonStats' => $salespersonStats,
            'monthlySalesComparison' => $monthlySalesComparison,
            'monthlySalesMax' => $monthlySalesMax,
            'monthlySalesCurrentColor' => $monthlySalesColors['current'],
            'monthlySalesPreviousColor' => $monthlySalesColors['previous'],
            'placementEarnings' => $placementEarnings,
        ];
    }

    /**
     * Bookings and sales totals per salesperson on a platform within the current financial year.
     *
     * @return Collection<int, Salesperson>
     */
    private function salespersonStats(Platform $platform, Carbon $fyStart, Carbon $fyEnd): Collection
    {
        return Salesperson::query()
            ->withCount(['reservations as bookings_count' => function ($query) use ($platform, $fyStart, $fyEnd) {
                $query->where('platform_id', $platform->id)
                    ->whereBetween('created_at', [$fyStart, $fyEnd]);
            }])
            ->withSum(['reservations as sales_total' => function ($query) use ($platform, $fyStart, $fyEnd) {
                $query->where('platform_id', $platform->id)
                    ->whereBetween('created_at', [$fyStart, $fyEnd]);
            }], 'gross_amount')
            ->orderByDesc('sales_total')
            ->get();
    }

    /**
     * Monthly sales on a platform for the current and previous FYs, aligned by FY month index.
     *
     * @return list<array{label: string, current: float, previous: float}>
     */
    private function monthlySalesComparison(
        Platform $platform,
        int $financialYearStart,
        Carbon $fyStart,
        Carbon $fyEnd,
        Carbon $prevFyStart,
        Carbon $prevFyEnd,
    ): array {
        $currentByYearMonth = $this->monthlyTotals($platform, $fyStart, $fyEnd);
        $previousByYearMonth = $this->monthlyTotals($platform, $prevFyStart, $prevFyEnd);

        $rows = [];
        for ($i = 0; $i < 12; $i++) {
            $currentMonth = Carbon::create($financialYearStart, Budget::FINANCIAL_YEAR_START_MONTH, 1)->addMonths($i);
            $previousMonth = $currentMonth->copy()->subYear();

            $rows[] = [
                'label' => $currentMonth->format('M'),
                'current' => (float) ($currentByYearMonth[$currentMonth->year.'-'.$currentMonth->month] ?? 0),
                'previous' => (float) ($previousByYearMonth[$previousMonth->year.'-'.$previousMonth->month] ?? 0),
            ];
        }

        return $rows;
    }

    /**
     * Group reservation gross_amount totals by year-month for a platform within a range.
     *
     * @return array<string, float>
     */
    private function monthlyTotals(Platform $platform, Carbon $start, Carbon $end): array
    {
        return Reservation::query()
            ->where('platform_id', $platform->id)
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw("strftime('%Y', created_at) as y, strftime('%m', created_at) as m, SUM(gross_amount) as total")
            ->groupBy('y', 'm')
            ->get()
            ->mapWithKeys(fn ($row) => [((int) $row->y).'-'.((int) $row->m) => (float) $row->total])
            ->all();
    }

    /**
     * Sum of reservation earnings grouped by placement type (Web / Social Media) for a platform.
     *
     * @return array<string, float>
     */
    private function placementEarnings(Platform $platform, Carbon $fyStart, Carbon $fyEnd): array
    {
        $totals = Reservation::query()
            ->join('placements', 'reservations.placement_id', '=', 'placements.id')
            ->where('reservations.platform_id', $platform->id)
            ->whereBetween('reservations.created_at', [$fyStart, $fyEnd])
            ->selectRaw('placements.type as type, SUM(reservations.gross_amount) as total')
            ->groupBy('placements.type')
            ->pluck('total', 'type')
            ->map(fn ($total) => (float) $total)
            ->all();

        return [
            PlacementType::Web->value => (float) ($totals[PlacementType::Web->value] ?? 0),
            PlacementType::SocialMedia->value => (float) ($totals[PlacementType::SocialMedia->value] ?? 0),
        ];
    }

    /**
     * Share of the financial year that has elapsed as of $now, expressed as a percentage.
     */
    private function expectedYearlyPercentage(Carbon $fyStart, Carbon $fyEnd, Carbon $now): float
    {
        $fyTotalDays = max(1, (int) round($fyStart->diffInDays($fyStart->copy()->addYear())));
        $daysElapsed = (int) round($fyStart->diffInDays($now));
        $daysElapsed = max(0, min($fyTotalDays, $daysElapsed));

        return ($daysElapsed / $fyTotalDays) * 100;
    }

    /**
     * Classify the yearly target as realisable, below average, or unrealistic,
     * based on actual progress vs the share of the FY that has elapsed. When no
     * budget has been set, the state is neutral — there is nothing to measure
     * against yet.
     */
    private function yearlyTargetState(float $yearlyBudget, float $yearlyPercentage, float $expectedYearlyPercentage): string
    {
        if ($yearlyBudget <= 0) {
            return 'neutral';
        }

        // Within the first few weeks of the FY there isn't enough signal to judge.
        if ($expectedYearlyPercentage < 5) {
            return 'realisable';
        }

        $ratio = $yearlyPercentage / $expectedYearlyPercentage;

        return match (true) {
            $ratio >= 0.9 => 'realisable',
            $ratio >= 0.6 => 'below_average',
            default => 'unrealistic',
        };
    }
}
