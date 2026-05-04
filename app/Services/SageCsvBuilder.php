<?php

namespace App\Services;

use App\CommissionType;
use App\DiscountType;
use App\Models\Reservation;
use App\ReservationStatus;
use App\ReservationType;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Build the SAGE-formatted CSV rows for a date range.
 *
 * The Credit export emits, per reservation:
 *   V;LG01;INV;1;{sage_client_code};{YYYYMMDD today};{dd-Mon-yyyy} To {dd-Mon-yyyy}
 *
 * followed by one D + one LC pair per booked day that falls inside the range:
 *   D;MUTLTIM;1;{daily_gross};{commission_pct};{discount_pct};{salesperson_code};{description}
 *   LC;DPT;PRD;SNM;MUL
 *
 * The 8th column of the D row is a `||`-separated description that includes
 * the booked date in DD-MM-YYYY form so the line item is self-describing.
 *
 * Cost of Artwork reservations are billed as a single line item (1 V + 1 D +
 * 1 LC per reservation, regardless of how many dates fall in the range) using
 * the user-entered `gross_amount`.
 *
 * Filter rules:
 * - Only Confirmed reservations are included.
 * - If bill_at_end_of_campaign is set and the campaign ends after the range,
 *   the reservation is excluded entirely.
 * - Reservations whose booked dates do not intersect the range are excluded.
 */
class SageCsvBuilder
{
    public function __construct(
        private readonly Carbon $start,
        private readonly Carbon $end,
        private readonly ?Carbon $generatedAt = null,
    ) {}

    /**
     * Build the rows for every confirmed reservation in the given collection.
     *
     * @param  Collection<int, Reservation>  $reservations
     * @return array<int, array<int, string>>
     */
    public function build(Collection $reservations): array
    {
        $now = $this->generatedAt ?? Carbon::now();

        /** @var Collection<int, Reservation> $eligible */
        $eligible = $reservations
            ->filter(fn (Reservation $r) => $r->status === ReservationStatus::Confirmed)
            ->filter(fn (Reservation $r) => ! $this->isExcludedByBillAtEnd($r))
            ->map(function (Reservation $r) {
                $r->setAttribute('__dates_in_range', $this->datesInRange($r));

                return $r;
            })
            ->filter(fn (Reservation $r) => $this->hasChargeableDates($r))
            ->sortBy(fn (Reservation $r) => [
                $r->client?->sage_client_code ?? 'zzz',
                $r->client?->company_name ?? '',
                $r->reference ?? '',
            ])
            ->values();

        $rows = [];
        foreach ($eligible as $reservation) {
            $rows[] = [
                'V',
                'LG01',
                'INV',
                '1',
                (string) ($reservation->client?->sage_client_code ?? ''),
                $now->format('Ymd'),
                $this->start->format('d-M-Y').' To '.$this->end->format('d-M-Y'),
            ];

            if ($reservation->type === ReservationType::CostOfArtwork) {
                /** @var Collection<int, Carbon> $datesInRange */
                $datesInRange = $reservation->getAttribute('__dates_in_range');
                $primaryDate = $datesInRange->first();

                $rows[] = [
                    'D',
                    'MUTLTIM',
                    '1',
                    $this->formatNumber((float) $reservation->gross_amount),
                    '0',
                    '0',
                    (string) ($reservation->salesperson?->sage_salesperson_code ?? ''),
                    $this->description($reservation, $primaryDate),
                ];

                $rows[] = ['LC', 'DPT', 'PRD', 'SNM', 'MUL'];

                continue;
            }

            /** @var Collection<int, Carbon> $datesInRange */
            $datesInRange = $reservation->getAttribute('__dates_in_range');
            $totalGross = $this->grossInRange($reservation);
            $commissionPct = $this->commissionPercent($reservation, $totalGross);
            $discountPct = $this->discountPercent($reservation, $totalGross);
            $dailyGross = (float) ($reservation->placement?->price ?? 0);

            foreach ($datesInRange as $date) {
                $rows[] = [
                    'D',
                    'MUTLTIM',
                    '1',
                    $this->formatNumber($dailyGross),
                    $this->formatNumber($commissionPct),
                    $this->formatNumber($discountPct),
                    (string) ($reservation->salesperson?->sage_salesperson_code ?? ''),
                    $this->description($reservation, $date),
                ];

                $rows[] = ['LC', 'DPT', 'PRD', 'SNM', 'MUL'];
            }
        }

        return $rows;
    }

    /**
     * Build the `||`-separated description for the D row's 8th column.
     *
     * Segments (in order): product, platform name, booked date (DD-MM-YYYY),
     * placement name, and the reservation reference.
     */
    private function description(Reservation $reservation, ?Carbon $date): string
    {
        $platformName = $reservation->placement?->platform?->name
            ?? $reservation->platform?->name
            ?? '';

        $segments = [
            (string) ($reservation->product ?? ''),
            (string) $platformName,
            $date?->format('d-m-Y') ?? '',
            (string) ($reservation->placement?->name ?? ''),
            'Ref. No '.($reservation->reference ?? ''),
        ];

        return implode('|| ', $segments);
    }

    /**
     * @return Collection<int, Carbon>
     */
    private function datesInRange(Reservation $reservation): Collection
    {
        return collect($reservation->dates_booked ?? [])
            ->map(fn ($date) => $date instanceof Carbon ? $date : Carbon::parse((string) $date))
            ->filter(fn (Carbon $date) => $date->betweenIncluded($this->start, $this->end))
            ->sort()
            ->values();
    }

    private function isExcludedByBillAtEnd(Reservation $reservation): bool
    {
        if (! $reservation->bill_at_end_of_campaign) {
            return false;
        }

        $lastBooked = collect($reservation->dates_booked ?? [])
            ->map(fn ($date) => $date instanceof Carbon ? $date : Carbon::parse((string) $date))
            ->max();

        if ($lastBooked === null) {
            return false;
        }

        return $lastBooked->greaterThan($this->end);
    }

    private function hasChargeableDates(Reservation $reservation): bool
    {
        /** @var Collection<int, Carbon> $datesInRange */
        $datesInRange = $reservation->getAttribute('__dates_in_range');

        return $datesInRange->isNotEmpty();
    }

    private function grossInRange(Reservation $reservation): float
    {
        /** @var Collection<int, Carbon> $datesInRange */
        $datesInRange = $reservation->getAttribute('__dates_in_range');

        if ($reservation->type === ReservationType::CostOfArtwork) {
            return (float) $reservation->gross_amount;
        }

        $dailyRate = (float) ($reservation->placement?->price ?? 0);

        return round($dailyRate * $datesInRange->count(), 2);
    }

    private function commissionPercent(Reservation $reservation, float $grossInRange): float
    {
        if ($reservation->type === ReservationType::CostOfArtwork) {
            return 0.0;
        }

        $client = $reservation->client;
        if ($client === null) {
            return 0.0;
        }

        $amount = (float) ($client->commission_amount ?? 0);
        if ($amount <= 0) {
            return 0.0;
        }

        if ($client->commission_type === CommissionType::Percentage) {
            return round($amount, 2);
        }

        if ($grossInRange <= 0) {
            return 0.0;
        }

        return round(($amount / $grossInRange) * 100, 2);
    }

    private function discountPercent(Reservation $reservation, float $grossInRange): float
    {
        if ($reservation->type === ReservationType::CostOfArtwork) {
            return 0.0;
        }

        $client = $reservation->client;
        if ($client === null) {
            return 0.0;
        }

        $amount = (float) ($client->discount ?? 0);
        if ($amount <= 0) {
            return 0.0;
        }

        if ($client->discount_type === DiscountType::Percentage) {
            return round($amount, 2);
        }

        if ($grossInRange <= 0) {
            return 0.0;
        }

        return round(($amount / $grossInRange) * 100, 2);
    }

    private function formatNumber(float $value): string
    {
        if (floor($value) === $value) {
            return (string) (int) $value;
        }

        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
    }
}
