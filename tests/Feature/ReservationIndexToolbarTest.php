<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders the export form for admin users', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->get(route('reservations.index'));

    $response->assertOk()
        ->assertSee('Export')
        ->assertSee('name="start_date"', false)
        ->assertSee('name="end_date"', false)
        ->assertSee('name="export_type"', false)
        ->assertSee('value="sage"', false)
        ->assertSee('value="sales"', false);
});

it('hides the export form for users who cannot sage-export', function () {
    $salesperson = User::factory()->salesperson()->create();

    $response = $this->actingAs($salesperson)->get(route('reservations.index'));

    $response->assertOk()
        ->assertDontSee('name="export_type"', false)
        ->assertSee('Add Reservation');
});

it('shows the Sales TBD error flash when the controller redirects back with error', function () {
    $admin = User::factory()->admin()->create();
    $message = 'Sales export is not yet available — the format is pending.';

    $response = $this->actingAs($admin)
        ->withSession(['error' => $message])
        ->get(route('reservations.index'));

    $response->assertOk()
        ->assertSee($message);
});
