<?php

namespace Database\Seeders;

use App\Models\Budget;
use App\Models\Platform;
use App\Models\Salesperson;
use App\Models\SalespersonTarget;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

class BudgetSeeder extends Seeder
{
    /**
     * Monthly base budget per platform (MUR).
     *
     * @var array<string, int>
     */
    private array $platformBaseBudgets = [
        'lexpress.mu' => 450_000,
        '5plus.mu' => 200_000,
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $financialYears = [
            Carbon::create(2024, 7, 1),
            Carbon::create(2025, 7, 1),
        ];

        $platforms = Platform::query()->whereIn('name', array_keys($this->platformBaseBudgets))->get();
        $salespeople = Salesperson::query()->get();

        foreach ($platforms as $platform) {
            $base = $this->platformBaseBudgets[$platform->name];
            $variation = (int) round($base * 0.2);

            foreach ($financialYears as $start) {
                for ($i = 0; $i < 12; $i++) {
                    $month = $start->copy()->addMonths($i);
                    $amount = $base + random_int(-$variation, $variation);

                    $budget = Budget::query()->updateOrCreate(
                        [
                            'platform_id' => $platform->id,
                            'year' => $month->year,
                            'month' => $month->month,
                        ],
                        ['amount' => $amount],
                    );

                    $this->createSalespersonTargets($budget, $salespeople);
                }
            }
        }
    }

    /**
     * Distribute a budget across all salespeople with some random variation.
     *
     * @param  Collection<int, Salesperson>  $salespeople
     */
    private function createSalespersonTargets(Budget $budget, $salespeople): void
    {
        if ($salespeople->isEmpty()) {
            return;
        }

        $remaining = (float) $budget->amount;
        $count = $salespeople->count();

        foreach ($salespeople as $index => $salesperson) {
            if ($index === $count - 1) {
                $target = round($remaining, 2);
            } else {
                $evenShare = $remaining / ($count - $index);
                $variation = $evenShare * 0.3;
                $target = round(max(0, $evenShare + random_int((int) -$variation, (int) $variation)), 2);
                $remaining -= $target;
            }

            SalespersonTarget::query()->updateOrCreate(
                [
                    'budget_id' => $budget->id,
                    'salesperson_id' => $salesperson->id,
                ],
                ['amount' => $target],
            );
        }
    }
}
