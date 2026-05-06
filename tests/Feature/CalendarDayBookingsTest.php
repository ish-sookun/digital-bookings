<?php

use App\Models\Client;
use App\Models\Placement;
use App\Models\Platform;
use App\Models\Reservation;
use App\Models\User;
use App\PlacementType;
use App\ReservationStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    $this->date = '2026-05-15';
    $this->client = Client::factory()->create(['company_name' => 'Acme Corp']);

    $this->lexpress = Platform::factory()->create(['name' => 'lexpress.mu']);
    $this->fivePlus = Platform::factory()->create(['name' => '5plus.mu']);

    $this->lexpressWeb = Placement::factory()->for($this->lexpress)->create([
        'name' => 'Lexpress Top Banner',
        'type' => PlacementType::Web,
    ]);
    $this->lexpressSocial = Placement::factory()->for($this->lexpress)->create([
        'name' => 'Lexpress Facebook Post',
        'type' => PlacementType::SocialMedia,
    ]);
    $this->fivePlusWeb = Placement::factory()->for($this->fivePlus)->create([
        'name' => '5plus Homepage Banner',
        'type' => PlacementType::Web,
    ]);
    $this->fivePlusSocial = Placement::factory()->for($this->fivePlus)->create([
        'name' => '5plus Instagram Post',
        'type' => PlacementType::SocialMedia,
    ]);
});

it('exposes a per-day booking map keyed by date', function () {
    Reservation::factory()->create([
        'product' => 'Lexpress Web Banner Campaign',
        'client_id' => $this->client->id,
        'platform_id' => $this->lexpress->id,
        'placement_id' => $this->lexpressWeb->id,
        'dates_booked' => [$this->date],
        'status' => ReservationStatus::Confirmed,
    ]);

    $response = $this->get(route('calendar.index', ['year' => 2026, 'month' => 5]));

    $response->assertOk();
    $bookings = $response->viewData('bookingsByDate');

    expect($bookings)->toBeArray()
        ->and($bookings)->toHaveKey($this->date);

    $sections = $bookings[$this->date];
    expect($sections)->toHaveCount(2)
        ->and($sections[0]['name'])->toBe('lexpress.mu')
        ->and($sections[1]['name'])->toBe('5plus.mu');
});

it('groups bookings by platform and placement type for a clicked day', function () {
    $lexWeb = Reservation::factory()->create([
        'product' => 'Lexpress Web Banner',
        'client_id' => $this->client->id,
        'platform_id' => $this->lexpress->id,
        'placement_id' => $this->lexpressWeb->id,
        'dates_booked' => [$this->date],
        'status' => ReservationStatus::Confirmed,
    ]);
    $lexSoc = Reservation::factory()->create([
        'product' => 'Lexpress FB Promo',
        'client_id' => $this->client->id,
        'platform_id' => $this->lexpress->id,
        'placement_id' => $this->lexpressSocial->id,
        'dates_booked' => [$this->date],
        'status' => ReservationStatus::Option,
    ]);
    $fivePlusWeb = Reservation::factory()->create([
        'product' => '5plus Homepage Banner',
        'client_id' => $this->client->id,
        'platform_id' => $this->fivePlus->id,
        'placement_id' => $this->fivePlusWeb->id,
        'dates_booked' => [$this->date],
        'status' => ReservationStatus::Confirmed,
    ]);
    $fivePlusSoc = Reservation::factory()->create([
        'product' => '5plus IG Story',
        'client_id' => $this->client->id,
        'platform_id' => $this->fivePlus->id,
        'placement_id' => $this->fivePlusSocial->id,
        'dates_booked' => [$this->date],
        'status' => ReservationStatus::Canceled,
    ]);

    $response = $this->get(route('calendar.index', ['year' => 2026, 'month' => 5]));

    $sections = $response->viewData('bookingsByDate')[$this->date];

    [$lexpressSection, $fivePlusSection] = $sections;

    expect($lexpressSection['groups'][0]['type'])->toBe('Web')
        ->and($lexpressSection['groups'][0]['reservations'])->toHaveCount(1)
        ->and($lexpressSection['groups'][0]['reservations'][0]['id'])->toBe($lexWeb->id)
        ->and($lexpressSection['groups'][0]['reservations'][0]['product'])->toBe('Lexpress Web Banner')
        ->and($lexpressSection['groups'][0]['reservations'][0]['placement'])->toBe('Lexpress Top Banner');

    expect($lexpressSection['groups'][1]['type'])->toBe('Social Media')
        ->and($lexpressSection['groups'][1]['reservations'])->toHaveCount(1)
        ->and($lexpressSection['groups'][1]['reservations'][0]['id'])->toBe($lexSoc->id)
        ->and($lexpressSection['groups'][1]['reservations'][0]['placement'])->toBe('Lexpress Facebook Post');

    expect($fivePlusSection['groups'][0]['type'])->toBe('Web')
        ->and($fivePlusSection['groups'][0]['reservations'])->toHaveCount(1)
        ->and($fivePlusSection['groups'][0]['reservations'][0]['id'])->toBe($fivePlusWeb->id);

    expect($fivePlusSection['groups'][1]['type'])->toBe('Social Media')
        ->and($fivePlusSection['groups'][1]['reservations'])->toHaveCount(1)
        ->and($fivePlusSection['groups'][1]['reservations'][0]['id'])->toBe($fivePlusSoc->id);
});

it('still shows both platform sections when only one has bookings on a day', function () {
    Reservation::factory()->create([
        'product' => 'Lexpress Solo',
        'client_id' => $this->client->id,
        'platform_id' => $this->lexpress->id,
        'placement_id' => $this->lexpressWeb->id,
        'dates_booked' => [$this->date],
        'status' => ReservationStatus::Confirmed,
    ]);

    $response = $this->get(route('calendar.index', ['year' => 2026, 'month' => 5]));

    [$lexpressSection, $fivePlusSection] = $response->viewData('bookingsByDate')[$this->date];

    expect($lexpressSection['groups'][0]['reservations'])->toHaveCount(1)
        ->and($lexpressSection['groups'][1]['reservations'])->toBeEmpty()
        ->and($fivePlusSection['groups'][0]['reservations'])->toBeEmpty()
        ->and($fivePlusSection['groups'][1]['reservations'])->toBeEmpty();
});

it('renders the day modal markup with platform and category headings', function () {
    $reservation = Reservation::factory()->create([
        'product' => 'Lexpress Banner',
        'client_id' => $this->client->id,
        'platform_id' => $this->lexpress->id,
        'placement_id' => $this->lexpressWeb->id,
        'dates_booked' => [$this->date],
        'status' => ReservationStatus::Confirmed,
    ]);

    $this->get(route('calendar.index', ['year' => 2026, 'month' => 5]))
        ->assertOk()
        ->assertSee('openDay(', false)
        ->assertSee('selectedDateLabel', false)
        ->assertSee('lexpress.mu')
        ->assertSee('5plus.mu')
        ->assertSee('Lexpress Banner')
        ->assertSee((string) $reservation->id)
        ->assertSee('Acme Corp');
});

it('respects the platform filter in the day booking map', function () {
    Reservation::factory()->create([
        'product' => 'Lexpress Web',
        'client_id' => $this->client->id,
        'platform_id' => $this->lexpress->id,
        'placement_id' => $this->lexpressWeb->id,
        'dates_booked' => [$this->date],
        'status' => ReservationStatus::Confirmed,
    ]);
    Reservation::factory()->create([
        'product' => '5plus Web',
        'client_id' => $this->client->id,
        'platform_id' => $this->fivePlus->id,
        'placement_id' => $this->fivePlusWeb->id,
        'dates_booked' => [$this->date],
        'status' => ReservationStatus::Confirmed,
    ]);

    $response = $this->get(route('calendar.index', [
        'year' => 2026,
        'month' => 5,
        'platform_id' => $this->lexpress->id,
    ]));

    [$lexpressSection, $fivePlusSection] = $response->viewData('bookingsByDate')[$this->date];

    expect($lexpressSection['groups'][0]['reservations'])->toHaveCount(1)
        ->and($fivePlusSection['groups'][0]['reservations'])->toBeEmpty()
        ->and($fivePlusSection['groups'][1]['reservations'])->toBeEmpty();
});
