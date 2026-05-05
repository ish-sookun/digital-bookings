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

it('only counts confirmed reservations in the sales-performance CSV export', function () {
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

    $response = $this->actingAs(User::factory()->superAdmin()->create())
        ->get(route('sales-performance.export', [
            'platform_id' => $platform->id,
            'format' => 'csv',
        ]));

    $response->assertSuccessful();
    $body = $response->streamedContent();

    // The Confirmed amount appears in the CSV (formatted with 2 dp by the controller).
    expect($body)->toContain('12345.00');

    // Option and Canceled amounts are excluded entirely.
    expect($body)->not->toContain('99999.00');
    expect($body)->not->toContain('88888.00');

    // The combined "all statuses" total should never appear either.
    $combined = number_format($confirmed + $option + $canceled, 2, '.', '');
    expect($body)->not->toContain($combined);
});

it('forbids non-management users from exporting sales performance', function () {
    $platform = Platform::factory()->create();

    $this->actingAs(User::factory()->salesperson()->create())
        ->get(route('sales-performance.export', [
            'platform_id' => $platform->id,
            'format' => 'csv',
        ]))
        ->assertForbidden();
});
