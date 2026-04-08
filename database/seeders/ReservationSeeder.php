<?php

namespace Database\Seeders;

use App\Models\Agency;
use App\Models\Client;
use App\Models\Placement;
use App\Models\Platform;
use App\Models\Reservation;
use App\Models\Salesperson;
use App\ReservationStatus;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReservationSeeder extends Seeder
{
    /**
     * @var list<string>
     */
    private array $products = [
        'Summer Sale Campaign',
        'Brand Awareness Drive',
        'Product Launch',
        'Back to School',
        'Year-End Promotion',
        'New Year Campaign',
        'Holiday Special',
        'Flash Sale',
        'Corporate Rebranding',
        'Festival Marketing',
        'Tourism Drive',
        'Automotive Launch',
        'Real Estate Showcase',
        'Banking Services',
        'Insurance Products',
        'Retail Discounts',
        'Food & Beverage Promo',
        'Fashion Collection',
        'Tech Expo',
        'Health Awareness',
    ];

    /**
     * @var list<string>
     */
    private array $channels = ['Run of site', 'Home & multimedia'];

    /**
     * @var list<string>
     */
    private array $scopes = ['Mauritius only', 'Worldwide'];

    /**
     * Share of monthly reservations allocated to lexpress.mu (vs 5plus.mu).
     */
    private const LEXPRESS_SHARE = 0.65;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->ensureSupportingData();

        /** @var list<int> $clientIds */
        $clientIds = Client::query()->pluck('id')->all();

        /** @var list<int> $agencyIds */
        $agencyIds = Agency::query()->pluck('id')->all();

        /** @var list<int> $salespersonIds */
        $salespersonIds = Salesperson::query()->pluck('id')->all();

        $lexpress = Platform::query()->where('name', 'lexpress.mu')->first();
        $fivePlus = Platform::query()->where('name', '5plus.mu')->first();

        if ($lexpress === null || $fivePlus === null || $salespersonIds === [] || $clientIds === []) {
            return;
        }

        /** @var Collection<int, Placement> $lexpressPlacements */
        $lexpressPlacements = Placement::query()->where('platform_id', $lexpress->id)->get();

        /** @var Collection<int, Placement> $fivePlusPlacements */
        $fivePlusPlacements = Placement::query()->where('platform_id', $fivePlus->id)->get();

        if ($lexpressPlacements->isEmpty() || $fivePlusPlacements->isEmpty()) {
            return;
        }

        $startDate = Carbon::now()->subYears(3)->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        DB::transaction(function () use (
            $startDate,
            $endDate,
            $lexpressPlacements,
            $fivePlusPlacements,
            $clientIds,
            $agencyIds,
            $salespersonIds,
        ): void {
            $current = $startDate->copy();
            $counter = 0;

            while ($current <= $endDate) {
                $monthsFromStart = (int) $startDate->diffInMonths($current);
                $total = $this->monthlyReservationCount($current->month, $monthsFromStart);

                $lexpressCount = (int) round($total * self::LEXPRESS_SHARE);
                $fivePlusCount = max(1, $total - $lexpressCount);

                $allocations = [
                    [$lexpressCount, $lexpressPlacements],
                    [$fivePlusCount, $fivePlusPlacements],
                ];

                foreach ($allocations as [$count, $platformPlacements]) {
                    for ($i = 0; $i < $count; $i++) {
                        $counter++;
                        $this->createReservation(
                            month: $current,
                            counter: $counter,
                            placement: $platformPlacements->random(),
                            clientIds: $clientIds,
                            agencyIds: $agencyIds,
                            salespersonIds: $salespersonIds,
                        );
                    }
                }

                $current->addMonth();
            }
        });
    }

    private function ensureSupportingData(): void
    {
        if (Client::query()->count() < 20) {
            Client::factory()->count(20 - Client::query()->count())->create();
        }

        if (Agency::query()->count() < 10) {
            Agency::factory()->count(10 - Agency::query()->count())->create();
        }
    }

    /**
     * Compute a monthly reservation count with growth and seasonal variation.
     */
    private function monthlyReservationCount(int $month, int $monthsFromStart): int
    {
        $baseCount = 10 + (int) floor($monthsFromStart / 3);

        $seasonalMultiplier = match ($month) {
            11, 12 => 1.5,
            6, 7 => 1.2,
            1, 2 => 0.7,
            default => 1.0,
        };

        $count = (int) round($baseCount * $seasonalMultiplier) + random_int(-2, 3);

        return max(5, $count);
    }

    /**
     * @param  list<int>  $clientIds
     * @param  list<int>  $agencyIds
     * @param  list<int>  $salespersonIds
     */
    private function createReservation(
        Carbon $month,
        int $counter,
        Placement $placement,
        array $clientIds,
        array $agencyIds,
        array $salespersonIds,
    ): void {
        $daysInMonth = $month->daysInMonth;
        $numDates = random_int(1, 7);
        $startDay = random_int(1, max(1, $daysInMonth - $numDates));

        $dates = [];
        for ($d = 0; $d < $numDates; $d++) {
            $dates[] = $month->copy()->day($startDay + $d)->format('Y-m-d');
        }

        $grossAmount = round((float) $placement->price * $numDates * (0.9 + (random_int(0, 30) / 100)), 2);
        $discount = round($grossAmount * (random_int(0, 15) / 100), 2);
        $commission = round($grossAmount * (random_int(5, 12) / 100), 2);
        $costOfArtwork = random_int(0, 3) === 0 ? (float) random_int(1000, 5000) : 0.0;

        $vatExempt = random_int(1, 100) <= 15;
        $netAfterDiscount = $grossAmount - $discount;
        $vat = $vatExempt ? 0.0 : round($netAfterDiscount * 0.15, 2);
        $totalAmountToPay = round($netAfterDiscount + $vat, 2);

        $createdAt = $month->copy()
            ->day(random_int(1, $daysInMonth))
            ->setTime(random_int(8, 18), random_int(0, 59), random_int(0, 59));

        $reference = $createdAt->timestamp.'-'.str_pad((string) $counter, 6, '0', STR_PAD_LEFT);

        $firstDate = Carbon::parse($dates[0]);
        $status = $this->pickStatus($firstDate);

        $reservation = new Reservation;
        $reservation->fill([
            'reference' => $reference,
            'client_id' => $clientIds[array_rand($clientIds)],
            'agency_id' => random_int(0, 1) === 0 && $agencyIds !== [] ? $agencyIds[array_rand($agencyIds)] : null,
            'salesperson_id' => $salespersonIds[array_rand($salespersonIds)],
            'product' => $this->products[array_rand($this->products)],
            'platform_id' => $placement->platform_id,
            'placement_id' => $placement->id,
            'channel' => $this->channels[array_rand($this->channels)],
            'scope' => $this->scopes[array_rand($this->scopes)],
            'dates_booked' => $dates,
            'gross_amount' => $grossAmount,
            'total_amount_to_pay' => $totalAmountToPay,
            'discount' => $discount,
            'commission' => $commission,
            'cost_of_artwork' => $costOfArtwork,
            'vat' => $vat,
            'vat_exempt' => $vatExempt,
            'status' => $status,
            'purchase_order_no' => 'PO-'.str_pad((string) random_int(1000, 9999), 4, '0', STR_PAD_LEFT),
            'invoice_no' => 'INV-'.str_pad((string) random_int(1000, 9999), 4, '0', STR_PAD_LEFT),
        ]);

        $reservation->created_at = $createdAt;
        $reservation->updated_at = $createdAt;
        $reservation->save();
    }

    /**
     * Pick a status biased by whether the booking's first date is in the past or future.
     *
     * Past bookings tend to be already Confirmed (with some Canceled), while future
     * bookings are more likely to still be Options.
     */
    private function pickStatus(Carbon $firstBookingDate): ReservationStatus
    {
        $roll = random_int(1, 100);

        if ($firstBookingDate->isPast()) {
            return match (true) {
                $roll <= 75 => ReservationStatus::Confirmed,
                $roll <= 90 => ReservationStatus::Canceled,
                default => ReservationStatus::Option,
            };
        }

        return match (true) {
            $roll <= 50 => ReservationStatus::Option,
            $roll <= 90 => ReservationStatus::Confirmed,
            default => ReservationStatus::Canceled,
        };
    }
}
