<?php

use App\Models\Budget;
use App\Models\Client;
use App\Models\Placement;
use App\Models\Platform;
use App\Models\Reservation;
use App\Models\Salesperson;
use App\Models\User;
use App\PlacementType;
use App\ReservationStatus;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Seed one Confirmed + one Option + one Canceled reservation for a single
 * salesperson on a single platform/placement, all dated mid-financial-year.
 *
 * Returns the inputs and the full per-status amounts so individual tests
 * can assert what should and shouldn't appear in each dashboard widget.
 *
 * @return array{platform: Platform, placement: Placement, salesperson: Salesperson, confirmed: int, option: int, canceled: int}
 */
function seedDashboardStatusFixtures(): array
{
    $platform = Platform::factory()->create(['name' => 'lexpress.mu']);
    $placement = Placement::factory()->create([
        'platform_id' => $platform->id,
        'type' => PlacementType::Web,
    ]);
    $salesperson = Salesperson::factory()->create([
        'first_name' => 'Alice',
        'last_name' => 'Anderson',
    ]);
    $client = Client::factory()->create();

    $fyStart = Budget::financialYearStartYear();
    $insideCurrentFy = Carbon::create($fyStart, Budget::FINANCIAL_YEAR_START_MONTH, 15);

    $confirmed = 12345;
    $option = 99999;
    $canceled = 88888;

    $cases = [
        ['amount' => $confirmed, 'status' => ReservationStatus::Confirmed],
        ['amount' => $option, 'status' => ReservationStatus::Option],
        ['amount' => $canceled, 'status' => ReservationStatus::Canceled],
    ];

    foreach ($cases as $case) {
        $reservation = Reservation::create([
            'client_id' => $client->id,
            'salesperson_id' => $salesperson->id,
            'platform_id' => $platform->id,
            'placement_id' => $placement->id,
            'product' => 'Test product',
            'channel' => 'Run of site',
            'scope' => 'Mauritius only',
            'dates_booked' => [$insideCurrentFy->format('Y-m-d')],
            'gross_amount' => $case['amount'],
            'total_amount_to_pay' => $case['amount'],
            'discount' => 0,
            'commission' => 0,
            'vat' => 0,
            'vat_exempt' => false,
            'status' => $case['status'],
        ]);
        $reservation->created_at = $insideCurrentFy;
        $reservation->updated_at = $insideCurrentFy;
        $reservation->saveQuietly();
    }

    return [
        'platform' => $platform,
        'placement' => $placement,
        'salesperson' => $salesperson,
        'confirmed' => $confirmed,
        'option' => $option,
        'canceled' => $canceled,
    ];
}

it('only counts confirmed reservations in the dashboard headline KPIs', function () {
    $fixtures = seedDashboardStatusFixtures();

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('home'))
        ->assertOk()
        ->assertSee('MUR '.number_format($fixtures['confirmed']))
        ->assertDontSee('MUR '.number_format($fixtures['option']))
        ->assertDontSee('MUR '.number_format($fixtures['canceled']));
});

it('only counts confirmed reservations in the salesperson performance card', function () {
    $fixtures = seedDashboardStatusFixtures();

    $response = $this->actingAs(User::factory()->admin()->create())
        ->get(route('home'))
        ->assertOk();

    // Alice's row exists with 1 reservation (the Confirmed one), not 3.
    $response->assertSee('Alice Anderson')
        ->assertSee('1 reservations')
        ->assertDontSee('2 reservations')
        ->assertDontSee('3 reservations');

    // The salesperson sales total uses the Confirmed amount only.
    $response->assertSee('MUR '.number_format($fixtures['confirmed']))
        ->assertDontSee('MUR '.number_format($fixtures['confirmed'] + $fixtures['option']))
        ->assertDontSee('MUR '.number_format($fixtures['confirmed'] + $fixtures['option'] + $fixtures['canceled']));
});

it('only counts confirmed reservations in sales by placement', function () {
    $fixtures = seedDashboardStatusFixtures();

    $response = $this->actingAs(User::factory()->admin()->create())
        ->get(route('home'))
        ->assertOk();

    // Web placement shows Confirmed amount only — never the sum that would
    // include Option / Canceled.
    $response->assertSee('Sales by Placement')
        ->assertSee('MUR '.number_format($fixtures['confirmed']));

    $confirmedPlusOption = $fixtures['confirmed'] + $fixtures['option'];
    $confirmedPlusBoth = $confirmedPlusOption + $fixtures['canceled'];
    $response->assertDontSee('MUR '.number_format($confirmedPlusOption))
        ->assertDontSee('MUR '.number_format($confirmedPlusBoth));
});

it('only counts confirmed reservations in the monthly sales comparison chart', function () {
    $fixtures = seedDashboardStatusFixtures();

    $response = $this->actingAs(User::factory()->admin()->create())
        ->get(route('home'))
        ->assertOk();

    // The chart bar tooltip carries the per-month MUR amount in the title attribute.
    $confirmedPlusOption = $fixtures['confirmed'] + $fixtures['option'];
    $confirmedPlusBoth = $confirmedPlusOption + $fixtures['canceled'];

    $response->assertSee('Monthly Sales Comparison')
        ->assertDontSee('MUR '.number_format($confirmedPlusOption), false)
        ->assertDontSee('MUR '.number_format($confirmedPlusBoth), false);
});
