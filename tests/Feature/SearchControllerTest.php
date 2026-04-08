<?php

use App\Models\Agency;
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

it('finds reservations matching the reference', function () {
    $user = User::factory()->salesperson()->create();
    $match = Reservation::factory()->create(['reference' => '1700000000-000042']);
    Reservation::factory()->create(['reference' => '1800000000-000099']);

    $this->actingAs($user)
        ->get(route('search.index', ['q' => '000042', 'type' => 'reservation']))
        ->assertOk()
        ->assertSee($match->reference)
        ->assertDontSee('1800000000-000099');
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

it('finds agencies by company name for admins', function () {
    $admin = User::factory()->admin()->create();
    Agency::factory()->create(['company_name' => 'Initech Media']);
    Agency::factory()->create(['company_name' => 'Umbrella Agency']);

    $this->actingAs($admin)
        ->get(route('search.index', ['q' => 'Initech', 'type' => 'agency']))
        ->assertOk()
        ->assertSee('Initech Media')
        ->assertDontSee('Umbrella Agency');
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

it('lets salespeople search agencies by company name', function () {
    $user = User::factory()->salesperson()->create();
    Agency::factory()->create(['company_name' => 'Initech Media']);
    Agency::factory()->create(['company_name' => 'Umbrella Agency']);

    $this->actingAs($user)
        ->get(route('search.index', ['q' => 'Initech', 'type' => 'agency']))
        ->assertOk()
        ->assertSee('Initech Media')
        ->assertDontSee('Umbrella Agency');
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
    Reservation::factory()->count(22)->create(['reference' => fake()->unique()->regexify('TESTREF-[0-9]{6}')]);

    $response = $this->actingAs($user)
        ->get(route('search.index', ['q' => 'TESTREF', 'type' => 'reservation']))
        ->assertOk();

    $response->assertSee('22 results found');
    $response->assertSee('Next');
});
