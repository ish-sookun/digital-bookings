<?php

use App\Models\Client;
use App\Models\Placement;
use App\Models\Reservation;
use App\Models\User;
use App\PlacementType;
use App\ReservationStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->admin()->create();
    $this->actingAs($this->user);
});

it('defaults the status column to Option for new rows in the database', function () {
    $client = Client::factory()->create();
    $placement = Placement::factory()->create();

    $reservation = new Reservation([
        'client_id' => $client->id,
        'product' => 'Test product',
        'placement_id' => $placement->id,
        'channel' => 'Run of site',
        'scope' => 'Mauritius only',
        'dates_booked' => ['2026-05-01'],
        'gross_amount' => 1000,
        'total_amount_to_pay' => 1150,
    ]);
    $reservation->save();

    expect($reservation->fresh()->status)->toBe(ReservationStatus::Option);
});

it('casts the status column to a ReservationStatus enum', function () {
    $reservation = Reservation::factory()->create(['status' => ReservationStatus::Confirmed]);

    expect($reservation->fresh()->status)->toBe(ReservationStatus::Confirmed);
});

it('lets a user create a reservation with a chosen status', function () {
    $client = Client::factory()->create();
    $placement = Placement::factory()->create();

    $response = $this->post(route('reservations.store'), [
        'client_id' => $client->id,
        'product' => 'Banner campaign',
        'placement_id' => $placement->id,
        'channel' => 'Run of site',
        'scope' => 'Mauritius only',
        'dates_booked' => json_encode(['2026-05-01']),
        'gross_amount' => 1000,
        'total_amount_to_pay' => 1150,
        'vat_exempt' => '0',
        'status' => ReservationStatus::Confirmed->value,
    ]);

    $response->assertRedirect(route('reservations.index'));

    $reservation = Reservation::where('product', 'Banner campaign')->firstOrFail();
    expect($reservation->status)->toBe(ReservationStatus::Confirmed);
});

it('lets a user update a reservation status', function () {
    $reservation = Reservation::factory()->create(['status' => ReservationStatus::Option]);

    $response = $this->put(route('reservations.update', $reservation), [
        'client_id' => $reservation->client_id,
        'product' => $reservation->product,
        'placement_id' => $reservation->placement_id,
        'channel' => $reservation->channel,
        'scope' => $reservation->scope,
        'dates_booked' => json_encode($reservation->dates_booked),
        'gross_amount' => $reservation->gross_amount,
        'total_amount_to_pay' => $reservation->total_amount_to_pay,
        'vat_exempt' => $reservation->vat_exempt ? '1' : '0',
        'status' => ReservationStatus::Canceled->value,
    ]);

    $response->assertRedirect(route('reservations.index'));
    expect($reservation->fresh()->status)->toBe(ReservationStatus::Canceled);
});

it('rejects an invalid status value on store', function () {
    $client = Client::factory()->create();
    $placement = Placement::factory()->create();

    $response = $this->post(route('reservations.store'), [
        'client_id' => $client->id,
        'product' => 'Bad status',
        'placement_id' => $placement->id,
        'channel' => 'Run of site',
        'scope' => 'Mauritius only',
        'dates_booked' => json_encode(['2026-05-01']),
        'gross_amount' => 1000,
        'total_amount_to_pay' => 1150,
        'vat_exempt' => '0',
        'status' => 'archived',
    ]);

    $response->assertSessionHasErrors('status');
    expect(Reservation::where('product', 'Bad status')->exists())->toBeFalse();
});

it('shows the status select on the create form', function () {
    $this->get(route('reservations.create'))
        ->assertOk()
        ->assertSee('Status')
        ->assertSee('Option')
        ->assertSee('Confirmed')
        ->assertSee('Canceled');
});

it('shows the status select on the edit form pre-selected with the current status', function () {
    $reservation = Reservation::factory()->create(['status' => ReservationStatus::Confirmed]);

    $this->get(route('reservations.edit', $reservation))
        ->assertOk()
        ->assertSee('Status')
        ->assertSee('value="confirmed" selected', false);
});

it('shows the status pill on the index page', function () {
    Reservation::factory()->create([
        'reference' => 'TEST-REF-1',
        'status' => ReservationStatus::Confirmed,
    ]);

    $this->get(route('reservations.index'))
        ->assertOk()
        ->assertSee('TEST-REF-1')
        ->assertSee('bg-green-50', false)
        ->assertSee('bg-green-500', false);
});

it('colors calendar entries by status', function () {
    $placement = Placement::factory()->create(['type' => PlacementType::Web]);
    Reservation::factory()->create([
        'product' => 'Calendar Test',
        'status' => ReservationStatus::Canceled,
        'placement_id' => $placement->id,
        'platform_id' => $placement->platform_id,
        'dates_booked' => [now()->startOfMonth()->format('Y-m-d')],
    ]);

    $this->get(route('calendar.index'))
        ->assertOk()
        ->assertSee('Calendar Test')
        ->assertSee('bg-red-100', false);
});

it('exposes a dot class for each status', function () {
    expect(ReservationStatus::Option->dotClasses())->toBe('bg-amber-500');
    expect(ReservationStatus::Confirmed->dotClasses())->toBe('bg-green-500');
    expect(ReservationStatus::Canceled->dotClasses())->toBe('bg-red-500');
});
