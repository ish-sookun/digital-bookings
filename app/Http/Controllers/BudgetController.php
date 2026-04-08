<?php

namespace App\Http\Controllers;

use App\Http\Requests\BudgetRequest;
use App\Models\Budget;
use App\Models\Platform;
use App\Models\Salesperson;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class BudgetController extends Controller
{
    /**
     * Display monthly budgets for a financial year, grouped by platform.
     */
    public function index(Request $request): View
    {
        $currentFinancialYearStart = Budget::financialYearStartYear();

        $financialYearStart = (int) $request->integer('fy', $currentFinancialYearStart);
        $this->validateFinancialYearStart($financialYearStart);

        $months = Budget::financialYearMonths($financialYearStart);
        $platforms = Platform::query()->orderBy('id')->get();

        $budgets = Budget::forFinancialYear($financialYearStart)
            ->with('salespersonTargets')
            ->get()
            ->groupBy('platform_id')
            ->map(fn (Collection $platformBudgets) => $platformBudgets->keyBy(
                fn (Budget $budget) => $budget->year.'-'.$budget->month
            ));

        $yearlyTotalsByPlatform = $platforms->mapWithKeys(fn (Platform $platform) => [
            $platform->id => (float) ($budgets->get($platform->id)?->sum('amount') ?? 0),
        ]);
        $yearlyTotal = (float) $yearlyTotalsByPlatform->sum();

        $financialYearLabel = Budget::financialYearLabel($financialYearStart);
        $isCurrentFinancialYear = $financialYearStart === $currentFinancialYearStart;
        $previousFinancialYearStart = $financialYearStart - 1;
        $nextFinancialYearStart = $financialYearStart + 1;

        return view('budgets.index', compact(
            'budgets',
            'months',
            'platforms',
            'yearlyTotal',
            'yearlyTotalsByPlatform',
            'financialYearLabel',
            'financialYearStart',
            'currentFinancialYearStart',
            'isCurrentFinancialYear',
            'previousFinancialYearStart',
            'nextFinancialYearStart',
        ));
    }

    /**
     * Show the form for editing a specific month's budget for a platform.
     */
    public function edit(Platform $platform, int $year, int $month): View
    {
        $this->validateYearMonth($year, $month);

        $budget = Budget::query()->firstOrNew([
            'platform_id' => $platform->id,
            'year' => $year,
            'month' => $month,
        ]);
        $budget->loadMissing('salespersonTargets');

        $salespeople = Salesperson::query()->orderBy('first_name')->get();

        $existingTargets = $budget->salespersonTargets->keyBy('salesperson_id');
        $monthLabel = Carbon::create($year, $month, 1)->format('F Y');
        $financialYearStart = Budget::financialYearStartYear(Carbon::create($year, $month, 1));

        return view('budgets.edit', compact('budget', 'platform', 'salespeople', 'existingTargets', 'year', 'month', 'monthLabel', 'financialYearStart'));
    }

    /**
     * Update the specified month's budget and salesperson targets for a platform.
     */
    public function update(BudgetRequest $request, Platform $platform, int $year, int $month): RedirectResponse
    {
        $this->validateYearMonth($year, $month);

        $budget = Budget::query()->updateOrCreate(
            [
                'platform_id' => $platform->id,
                'year' => $year,
                'month' => $month,
            ],
            ['amount' => $request->validated('amount')],
        );

        /** @var array<int, mixed> $targets */
        $targets = $request->validated('targets', []);

        foreach ($targets as $salespersonId => $amount) {
            if ($amount === null || $amount === '') {
                $budget->salespersonTargets()->where('salesperson_id', $salespersonId)->delete();

                continue;
            }

            $budget->salespersonTargets()->updateOrCreate(
                ['salesperson_id' => $salespersonId],
                ['amount' => $amount],
            );
        }

        $financialYearStart = Budget::financialYearStartYear(Carbon::create($year, $month, 1));

        return redirect()
            ->route('budgets.index', ['fy' => $financialYearStart])
            ->with('success', 'Budget updated successfully.');
    }

    private function validateYearMonth(int $year, int $month): void
    {
        abort_unless($month >= 1 && $month <= 12, 404);
        abort_unless($year >= 2000 && $year <= 2100, 404);
    }

    private function validateFinancialYearStart(int $year): void
    {
        abort_unless($year >= 2000 && $year <= 2100, 404);
    }
}
