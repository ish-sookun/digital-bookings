<?php

use App\Models\Client;
use App\Models\Placement;
use App\Models\Reservation;
use App\Models\User;
use App\ReservationStatus;
use App\ReservationType;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->admin()->create();
    $this->actingAs($this->user);
});

it('stores a reservation with a parent_reservation_id', function () {
    $parent = Reservation::factory()->create();
    $client = Client::factory()->create();
    $placement = Placement::factory()->create();

    $response = $this->post(route('reservations.store'), [
        'client_id' => $client->id,
        'product' => 'Linked child reservation',
        'placement_id' => $placement->id,
        'type' => ReservationType::Standard->value,
        'channel' => 'Run of site',
        'scope' => 'Mauritius only',
        'dates_booked' => json_encode(['2026-05-01']),
        'gross_amount' => 1000,
        'total_amount_to_pay' => 1150,
        'vat_exempt' => '0',
        'parent_reservation_id' => $parent->id,
        'status' => ReservationStatus::Option->value,
    ]);

    $response->assertRedirect(route('reservations.index'));

    $child = Reservation::where('product', 'Linked child reservation')->firstOrFail();
    expect($child->parent_reservation_id)->toBe($parent->id);
    expect($child->parent?->id)->toBe($parent->id);
});

it('rejects a non-existent parent_reservation_id', function () {
    $client = Client::factory()->create();
    $placement = Placement::factory()->create();

    $response = $this->post(route('reservations.store'), [
        'client_id' => $client->id,
        'product' => 'Bad parent link',
        'placement_id' => $placement->id,
        'type' => ReservationType::Standard->value,
        'channel' => 'Run of site',
        'scope' => 'Mauritius only',
        'dates_booked' => json_encode(['2026-05-01']),
        'gross_amount' => 1000,
        'total_amount_to_pay' => 1150,
        'vat_exempt' => '0',
        'parent_reservation_id' => 999999,
        'status' => ReservationStatus::Option->value,
    ]);

    $response->assertSessionHasErrors('parent_reservation_id');
    expect(Reservation::where('product', 'Bad parent link')->exists())->toBeFalse();
});

it('shows the parent reservation link on the child show page', function () {
    $parent = Reservation::factory()->create(['product' => 'Parent Campaign']);
    $child = Reservation::factory()->create(['parent_reservation_id' => $parent->id]);

    $this->get(route('reservations.show', $child))
        ->assertOk()
        ->assertSee('Parent Reservation')
        ->assertSee((string) $parent->id)
        ->assertSee('Parent Campaign');
});

it('exposes children relation on parent reservation', function () {
    $parent = Reservation::factory()->create();
    $child = Reservation::factory()->create(['parent_reservation_id' => $parent->id]);

    expect($parent->fresh()->children->pluck('id')->all())->toContain($child->id);
});
