<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Budget extends Model
{
    /** @use HasFactory<\Database\Factories\BudgetFactory> */
    use HasFactory;

    public const FINANCIAL_YEAR_START_MONTH = 7;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'platform_id',
        'year',
        'month',
        'amount',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'platform_id' => 'integer',
            'year' => 'integer',
            'month' => 'integer',
            'amount' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Platform, $this>
     */
    public function platform(): BelongsTo
    {
        return $this->belongsTo(Platform::class);
    }

    /**
     * @return HasMany<SalespersonTarget, $this>
     */
    public function salespersonTargets(): HasMany
    {
        return $this->hasMany(SalespersonTarget::class);
    }

    public function label(): string
    {
        return Carbon::create($this->year, $this->month, 1)->format('F Y');
    }

    /**
     * Determine the financial year start year for a given date.
     * Financial year runs from July to June.
     */
    public static function financialYearStartYear(?Carbon $date = null): int
    {
        $date ??= Carbon::now();

        return $date->month >= self::FINANCIAL_YEAR_START_MONTH
            ? $date->year
            : $date->year - 1;
    }

    /**
     * Get the months for the financial year that begins in July of $startYear.
     *
     * @return list<array{year: int, month: int, label: string}>
     */
    public static function financialYearMonths(int $startYear): array
    {
        $months = [];
        for ($i = 0; $i < 12; $i++) {
            $date = Carbon::create($startYear, self::FINANCIAL_YEAR_START_MONTH, 1)->addMonths($i);
            $months[] = [
                'year' => $date->year,
                'month' => $date->month,
                'label' => $date->format('F Y'),
            ];
        }

        return $months;
    }

    /**
     * @return list<array{year: int, month: int, label: string}>
     */
    public static function currentFinancialYearMonths(): array
    {
        return self::financialYearMonths(self::financialYearStartYear());
    }

    /**
     * Scope a query to budgets within the financial year that starts in $startYear.
     *
     * @return Builder<Budget>
     */
    public static function forFinancialYear(int $startYear): Builder
    {
        $endYear = $startYear + 1;

        return self::query()->where(function (Builder $query) use ($startYear, $endYear) {
            $query->where(function (Builder $q) use ($startYear) {
                $q->where('year', $startYear)
                    ->where('month', '>=', self::FINANCIAL_YEAR_START_MONTH);
            })->orWhere(function (Builder $q) use ($endYear) {
                $q->where('year', $endYear)
                    ->where('month', '<', self::FINANCIAL_YEAR_START_MONTH);
            });
        });
    }

    public static function financialYearLabel(int $startYear): string
    {
        return $startYear.'/'.($startYear + 1);
    }
}
