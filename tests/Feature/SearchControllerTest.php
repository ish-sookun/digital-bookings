<?php

use App\Models\Client;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirects guests to login', function () {
    $this->get(route('search.index'))->assertRedirect(route('login'));
});

it('shows an empty prompt when no query is provided', function () {
    $user = User::factory()->salesperson()->create();

    $this->actingAs($user)
        ->get(route('search.index'))
        ->assertOk()
        ->assertSee('Search Results')
        ->assertSee('Enter a search query');
});

it('finds reservations matching the id', function () {
    $user = User::factory()->salesperson()->create();
    Reservation::factory()->count(5)->create();
    $match = Reservation::factory()->create();

    $this->actingAs($user)
        ->get(route('search.index', ['q' => (string) $match->id, 'type' => 'reservation']))
        ->assertOk()
        ->assertSee((string) $match->id);
});

it('finds clients by company name for admins', function () {
    $admin = User::factory()->admin()->create();
    Client::factory()->create(['company_name' => 'Acme Industries Ltd']);
    Client::factory()->create(['company_name' => 'Globex Corporation']);

    $this->actingAs($admin)
        ->get(route('search.index', ['q' => 'Acme', 'type' => 'client']))
        ->assertOk()
        ->assertSee('Acme Industries Ltd')
        ->assertDontSee('Globex Corporation');
});

it('lets salespeople search clients by company name', function () {
    $user = User::factory()->salesperson()->create();
    Client::factory()->create(['company_name' => 'Acme Industries Ltd']);
    Client::factory()->create(['company_name' => 'Globex Corporation']);

    $this->actingAs($user)
        ->get(route('search.index', ['q' => 'Acme', 'type' => 'client']))
        ->assertOk()
        ->assertSee('Acme Industries Ltd')
        ->assertDontSee('Globex Corporation');
});

it('shows a no results message when nothing matches', function () {
    $user = User::factory()->salesperson()->create();

    $this->actingAs($user)
        ->get(route('search.index', ['q' => 'zzzzzzz', 'type' => 'reservation']))
        ->assertOk()
        ->assertSee('No results found');
});

it('paginates reservation results at 20 per page', function () {
    $user = User::factory()->salesperson()->create();
    Reservation::factory()->count(22)->create();

    // `%` matches every cast id under the LIKE clause.
    $response = $this->actingAs($user)
        ->get(route('search.index', ['q' => '%', 'type' => 'reservation']))
        ->assertOk();

    $response->assertSee('22 results found');
    $response->assertSee('Next');
});
