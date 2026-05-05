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
 * Eligibility and the dates that produce D + LC pairs depend on the
 * reservation type and the bill_at_end_of_campaign flag:
 *
 *   - Standard, bill_at_end_of_campaign = false: the reservation is included
 *     in **every** export whose date range overlaps any booked date, but only
 *     the booked dates that fall **inside** the export window produce D + LC
 *     pairs. A campaign that spans two months is therefore billed across two
 *     exports — never double-counted, never split off-period.
 *
 *   - Standard, bill_at_end_of_campaign = true: the reservation is billed
 *     exactly **once**, in the export whose window contains the **last**
 *     booked date. When billed, every booked date produces a D + LC pair —
 *     including dates that pre-date the export window (the whole campaign
 *     is invoiced at the end).
 *
 *   - Cost of Artwork: emits a single line item (1 V + 1 D + 1 LC) regardless
 *     of date count. Eligible exactly once: in the export whose window
 *     contains the first booked date by default, or the last booked date if
 *     bill_at_end_of_campaign is set. Uses `gross_amount` as the line total.
 *
 * Output shape
 * ------------
 * For each eligible reservation:
 *   V;LG01;INV;1;{sage_client_code};{YYYYMMDD today};{dd-Mon-yyyy} To {dd-Mon-yyyy}
 *   D;MULTIM-LSL;1;{daily_gross};{commission_pct};{discount_pct};{salesperson_code};{description}   <-- one per emitted day
 *   LC;DPT;PRD;SNM;MUL                                                                            <-- one after each D
 *
 * Only Confirmed reservations are exported.
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
            ->map(function (Reservation $r) {
                $r->setAttribute('__sage_dates_to_emit', $this->datesToEmit($r));

                return $r;
            })
            ->filter(fn (Reservation $r) => $r->getAttribute('__sage_dates_to_emit')->isNotEmpty())
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

            /** @var Collection<int, Carbon> $datesToEmit */
            $datesToEmit = $reservation->getAttribute('__sage_dates_to_emit');

            if ($reservation->type === ReservationType::CostOfArtwork) {
                $rows[] = [
                    'D',
                    'MULTIM-LSL',
                    '1',
                    $this->formatNumber((float) $reservation->gross_amount),
                    '0',
                    '0',
                    (string) ($reservation->salesperson?->sage_salesperson_code ?? ''),
                    $this->description($reservation, $datesToEmit->first()),
                ];

                $rows[] = ['LC', 'DPT', 'PRD', 'SNM', 'MUL'];

                continue;
            }

            $dailyGross = (float) ($reservation->placement?->price ?? 0);
            $totalGross = $this->totalReservationGross($reservation);
            $commissionPct = $this->commissionPercent($reservation, $totalGross);
            $discountPct = $this->discountPercent($reservation, $totalGross);

            foreach ($datesToEmit as $date) {
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
     * The set of booked dates to emit a D + LC pair for in this export.
     *
     * - Standard, no bill_at_end: only the booked dates that fall inside
     *   [start, end]. Empty collection ⇒ reservation excluded from this export.
     * - Standard, bill_at_end: all booked dates, but only when the last booked
     *   date falls inside [start, end] (otherwise empty ⇒ excluded).
     * - Cost of Artwork: a single-element collection containing the billing
     *   date (first or last booked date depending on bill_at_end), but only
     *   when that billing date falls inside [start, end].
     *
     * @return Collection<int, Carbon>
     */
    private function datesToEmit(Reservation $reservation): Collection
    {
        $allDates = $this->allDatesSorted($reservation);

        if ($allDates->isEmpty()) {
            return collect();
        }

        if ($reservation->type === ReservationType::CostOfArtwork) {
            $billingDate = $reservation->bill_at_end_of_campaign
                ? $allDates->last()
                : $allDates->first();

            return $billingDate->betweenIncluded($this->start, $this->end)
                ? collect([$billingDate])
                : collect();
        }

        if ($reservation->bill_at_end_of_campaign) {
            return $allDates->last()->betweenIncluded($this->start, $this->end)
                ? $allDates
                : collect();
        }

        return $allDates
            ->filter(fn (Carbon $date) => $date->betweenIncluded($this->start, $this->end))
            ->values();
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

    /**
     * Total reservation gross across **all** booked dates — the basis for
     * commission/discount percentage normalisation. Using the full reservation
     * total (rather than the in-range total) keeps the percentage stable
     * across exports when a Standard reservation is split across months.
     */
    private function totalReservationGross(Reservation $reservation): float
    {
        if ($reservation->type === ReservationType::CostOfArtwork) {
            return (float) $reservation->gross_amount;
        }

        $dailyRate = (float) ($reservation->placement?->price ?? 0);

        return round($dailyRate * $this->allDatesSorted($reservation)->count(), 2);
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
