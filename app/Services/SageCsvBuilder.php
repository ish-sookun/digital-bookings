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
 * Billing model
 * -------------
 * A reservation is billed in **exactly one** export — the one whose date
 * range covers the reservation's "billing date":
 *
 *   - Standard reservations: billing date = first booked date
 *   - bill_at_end_of_campaign: billing date = last booked date
 *
 * When billed, every booked date of the reservation produces a D + LC pair —
 * including dates that fall outside the export range (e.g. a campaign that
 * starts in this month but ends in a future month is billed in full this
 * month). A reservation whose billing date falls before the export's start
 * is therefore **excluded** entirely (it was billed in a previous export).
 *
 * Output shape
 * ------------
 * For each eligible reservation:
 *   V;LG01;INV;1;{sage_client_code};{YYYYMMDD today};{dd-Mon-yyyy} To {dd-Mon-yyyy}
 *   D;MULTIM-LSL;1;{daily_gross};{commission_pct};{discount_pct};{salesperson_code};{description}   <-- one per booked day
 *   LC;DPT;PRD;SNM;MUL                                                                            <-- one after each D
 *
 * Cost of Artwork reservations are billed as a single line item (1 V + 1 D +
 * 1 LC per reservation, regardless of how many dates the reservation has)
 * using the user-entered `gross_amount`.
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
            ->filter(fn (Reservation $r) => $this->isBillableInRange($r))
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

            $allDates = $this->allDatesSorted($reservation);

            if ($reservation->type === ReservationType::CostOfArtwork) {
                $primaryDate = $allDates->first();

                $rows[] = [
                    'D',
                    'MULTIM-LSL',
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

            $dailyGross = (float) ($reservation->placement?->price ?? 0);
            $totalGross = round($dailyGross * $allDates->count(), 2);
            $commissionPct = $this->commissionPercent($reservation, $totalGross);
            $discountPct = $this->discountPercent($reservation, $totalGross);

            foreach ($allDates as $date) {
                $rows[] = [
                    'D',
                    'MULTIM-LSL',
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
     * Decide whether a reservation should be billed in this export.
     *
     * - bill_at_end_of_campaign: billed when the LAST booked date falls inside
     *   the export window.
     * - Otherwise: billed when the FIRST booked date falls inside the window.
     *
     * Reservations whose billing date falls before the window are assumed to
     * have been billed in a previous export. Reservations whose billing date
     * falls after the window are billed in a future export.
     */
    private function isBillableInRange(Reservation $reservation): bool
    {
        $dates = $this->allDatesSorted($reservation);

        if ($dates->isEmpty()) {
            return false;
        }

        $billingDate = $reservation->bill_at_end_of_campaign
            ? $dates->last()
            : $dates->first();

        return $billingDate->betweenIncluded($this->start, $this->end);
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
    private function allDatesSorted(Reservation $reservation): Collection
    {
        return collect($reservation->dates_booked ?? [])
            ->map(fn ($date) => $date instanceof Carbon ? $date : Carbon::parse((string) $date))
            ->sort()
            ->values();
    }

    private function commissionPercent(Reservation $reservation, float $totalGross): float
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

        if ($totalGross <= 0) {
            return 0.0;
        }

        return round(($amount / $totalGross) * 100, 2);
    }

    private function discountPercent(Reservation $reservation, float $totalGross): float
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

        if ($totalGross <= 0) {
            return 0.0;
        }

        return round(($amount / $totalGross) * 100, 2);
    }

    private function formatNumber(float $value): string
    {
        if (floor($value) === $value) {
            return (string) (int) $value;
        }

        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
    }
}
