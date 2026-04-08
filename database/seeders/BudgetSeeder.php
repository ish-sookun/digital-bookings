<?php

namespace Database\Seeders;

use App\Models\Budget;
use App\Models\Platform;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class BudgetSeeder extends Seeder
{
    /**
     * Monthly base budget per platform (MUR).
     *
     * @var array<string, int>
     */
    private array $platformBaseBudgets = [
        'lexpress.mu' => 1_500_000,
        '5plus.mu' => 600_000,
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $start = Carbon::create(2025, 7, 1);
        $platforms = Platform::query()->whereIn('name', array_keys($this->platformBaseBudgets))->get();

        foreach ($platforms as $platform) {
            $base = $this->platformBaseBudgets[$platform->name];
            $variation = (int) round($base * 0.2);

            for ($i = 0; $i < 12; $i++) {
                $month = $start->copy()->addMonths($i);
                $amount = $base + random_int(-$variation, $variation);

                Budget::query()->updateOrCreate(
                    [
                        'platform_id' => $platform->id,
                        'year' => $month->year,
                        'month' => $month->month,
                    ],
                    ['amount' => $amount],
                );
            }
        }
    }
}
