<?php

use App\Models\Budget;
use App\Models\Placement;
use App\Models\Platform;
use App\Models\Reservation;
use App\Models\Salesperson;
use App\Models\SalespersonTarget;
use App\Models\User;
use App\PlacementType;
use App\UserRole;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows the budget index for super admins', function () {
    $superAdmin = User::factory()->superAdmin()->create();
    $platform = Platform::factory()->create(['name' => 'lexpress.mu']);
    $currentFinancialYearStart = Budget::financialYearStartYear();
    Budget::factory()->create([
        'platform_id' => $platform->id,
        'year' => $currentFinancialYearStart,
        'month' => 7,
        'amount' => 1500000,
    ]);

    $this->actingAs($superAdmin)
        ->get(route('budgets.index'))
        ->assertOk()
        ->assertSee('Budget')
        ->assertSee('lexpress.mu')
        ->assertSee('July '.$currentFinancialYearStart);
});

it('shows a different financial year via the fy query parameter', function () {
    $superAdmin = User::factory()->superAdmin()->create();
    $platform = Platform::factory()->create(['name' => 'lexpress.mu']);
    Budget::factory()->create([
        'platform_id' => $platform->id,
        'year' => 2030,
        'month' => 7,
        'amount' => 2000000,
    ]);

    $this->actingAs($superAdmin)
        ->get(route('budgets.index', ['fy' => 2030]))
        ->assertOk()
        ->assertSee('FY 2030/2031')
        ->assertSee('July 2030')
        ->assertSee('June 2031');
});

it('marks the current financial year as current and others as not current', function () {
    $superAdmin = User::factory()->superAdmin()->create();
    $currentFinancialYearStart = Budget::financialYearStartYear();

    $this->actingAs($superAdmin)
        ->get(route('budgets.index'))
        ->assertOk()
        ->assertSee('Current');

    $this->actingAs($superAdmin)
        ->get(route('budgets.index', ['fy' => $currentFinancialYearStart + 1]))
        ->assertOk()
        ->assertDontSee('Current')
        ->assertSee('Jump to current FY');
});

it('rejects an out-of-range financial year', function () {
    $superAdmin = User::factory()->superAdmin()->create();

    $this->actingAs($superAdmin)
        ->get(route('budgets.index', ['fy' => 1500]))
        ->assertNotFound();
});

it('redirects back to the financial year of the edited month after update', function () {
    $superAdmin = User::factory()->superAdmin()->create();
    $platform = Platform::factory()->create();

    $this->actingAs($superAdmin)
        ->put(route('budgets.update', ['platform' => $platform, 'year' => 2030, 'month' => 3]), [
            'amount' => 1900000,
        ])
        ->assertRedirect(route('budgets.index', ['fy' => 2029]));

    $this->actingAs($superAdmin)
        ->put(route('budgets.update', ['platform' => $platform, 'year' => 2030, 'month' => 8]), [
            'amount' => 1900000,
        ])
        ->assertRedirect(route('budgets.index', ['fy' => 2030]));
});

it('forbids non-super-admins from viewing budgets', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('budgets.index'))
        ->assertForbidden();
});

it('forbids salespersons from viewing budgets', function () {
    $salesperson = User::factory()->salesperson()->create();

    $this->actingAs($salesperson)
        ->get(route('budgets.index'))
        ->assertForbidden();
});

it('allows super admin to update a monthly budget and salesperson targets', function () {
    $superAdmin = User::factory()->superAdmin()->create();
    $salesperson = Salesperson::factory()->create();
    $platform = Platform::factory()->create();

    $this->actingAs($superAdmin)
        ->put(route('budgets.update', ['platform' => $platform, 'year' => 2025, 'month' => 7]), [
            'amount' => 1750000,
            'targets' => [
                $salesperson->id => 250000,
            ],
        ])
        ->assertRedirect(route('budgets.index', ['fy' => 2025]));

    expect(Budget::where('platform_id', $platform->id)->where('year', 2025)->where('month', 7)->first())
        ->amount->toEqual('1750000.00');

    expect(SalespersonTarget::where('salesperson_id', $salesperson->id)->first())
        ->amount->toEqual('250000.00');
});

it('keeps budgets for different platforms independent', function () {
    $superAdmin = User::factory()->superAdmin()->create();
    $lexpress = Platform::factory()->create(['name' => 'lexpress.mu']);
    $fivePlus = Platform::factory()->create(['name' => '5plus.mu']);

    $this->actingAs($superAdmin)
        ->put(route('budgets.update', ['platform' => $lexpress, 'year' => 2025, 'month' => 7]), [
            'amount' => 1000000,
        ]);

    $this->actingAs($superAdmin)
        ->put(route('budgets.update', ['platform' => $fivePlus, 'year' => 2025, 'month' => 7]), [
            'amount' => 500000,
        ]);

    expect(Budget::where('platform_id', $lexpress->id)->where('year', 2025)->where('month', 7)->value('amount'))
        ->toEqual('1000000.00');
    expect(Budget::where('platform_id', $fivePlus->id)->where('year', 2025)->where('month', 7)->value('amount'))
        ->toEqual('500000.00');
});

it('removes a salesperson target when set to empty', function () {
    $superAdmin = User::factory()->superAdmin()->create();
    $salesperson = Salesperson::factory()->create();
    $platform = Platform::factory()->create();
    $budget = Budget::factory()->create([
        'platform_id' => $platform->id,
        'year' => 2025,
        'month' => 8,
        'amount' => 1500000,
    ]);
    SalespersonTarget::factory()->create([
        'budget_id' => $budget->id,
        'salesperson_id' => $salesperson->id,
        'amount' => 100000,
    ]);

    $this->actingAs($superAdmin)
        ->put(route('budgets.update', ['platform' => $platform, 'year' => 2025, 'month' => 8]), [
            'amount' => 1500000,
            'targets' => [
                $salesperson->id => '',
            ],
        ])
        ->assertRedirect();

    expect(SalespersonTarget::where('salesperson_id', $salesperson->id)->count())->toBe(0);
});

it('forbids non-super-admins from updating a budget', function () {
    $admin = User::factory()->admin()->create();
    $platform = Platform::factory()->create();

    $this->actingAs($admin)
        ->put(route('budgets.update', ['platform' => $platform, 'year' => 2025, 'month' => 7]), [
            'amount' => 1750000,
        ])
        ->assertForbidden();
});

it('shows dashboard KPI cards on the home page', function () {
    $user = User::factory()->create(['role' => UserRole::Admin]);
    $platform = Platform::factory()->create(['name' => 'lexpress.mu']);
    Budget::factory()->create([
        'platform_id' => $platform->id,
        'year' => 2025,
        'month' => 7,
        'amount' => 1500000,
    ]);

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('Dashboard')
        ->assertSee('Yearly Budget')
        ->assertSee('Cumulated Sales')
        ->assertSee('Yearly Target');
});

it('shows both platform sections on the dashboard', function () {
    $user = User::factory()->create(['role' => UserRole::Admin]);
    Platform::factory()->create(['name' => 'lexpress.mu']);
    Platform::factory()->create(['name' => '5plus.mu']);

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertSeeInOrder(['Dashboard', 'lexpress.mu', '5plus.mu']);
});

it('shows the second row of dashboard cards on the home page', function () {
    $user = User::factory()->create(['role' => UserRole::Admin]);
    $fyStart = Budget::financialYearStartYear();
    $insideCurrentFy = Carbon::create($fyStart, Budget::FINANCIAL_YEAR_START_MONTH, 15);
    $insidePreviousFy = $insideCurrentFy->copy()->subYear();

    $salesperson = Salesperson::factory()->create([
        'first_name' => 'Alice',
        'last_name' => 'Anderson',
    ]);
    $platform = Platform::factory()->create(['name' => 'lexpress.mu']);

    $current = Reservation::factory()->create([
        'salesperson_id' => $salesperson->id,
        'platform_id' => $platform->id,
        'gross_amount' => 12345,
    ]);
    $current->created_at = $insideCurrentFy;
    $current->updated_at = $insideCurrentFy;
    $current->saveQuietly();

    $previous = Reservation::factory()->create([
        'salesperson_id' => $salesperson->id,
        'platform_id' => $platform->id,
        'gross_amount' => 6789,
    ]);
    $previous->created_at = $insidePreviousFy;
    $previous->updated_at = $insidePreviousFy;
    $previous->saveQuietly();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('Salesperson Performance')
        ->assertSee('Alice Anderson')
        ->assertSee('1 reservations')
        ->assertSee('Monthly Sales Comparison')
        ->assertSee('Sales by Placement')
        ->assertSee('lexpress.mu')
        ->assertSee('MUR '.number_format(12345))
        ->assertSee('MUR '.number_format(6789));
});

it('colours the yearly target based on actual vs expected progress', function (float $sales, string $expectedClass) {
    $this->travelTo(Carbon::create(2026, 1, 1));

    $user = User::factory()->create(['role' => UserRole::Admin]);
    $platform = Platform::factory()->create(['name' => 'lexpress.mu']);
    Budget::factory()->create([
        'platform_id' => $platform->id,
        'year' => 2025,
        'month' => 7,
        'amount' => 12_000_000,
    ]);

    if ($sales > 0) {
        $reservation = Reservation::factory()->create([
            'platform_id' => $platform->id,
            'gross_amount' => $sales,
        ]);
        $reservation->created_at = Carbon::create(2025, 9, 15);
        $reservation->updated_at = Carbon::create(2025, 9, 15);
        $reservation->saveQuietly();
    }

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertSee($expectedClass, false);
})->with([
    'realisable (on pace)' => [6_000_000, 'text-green-600'],
    'below average' => [4_500_000, 'text-amber-600'],
    'unrealistic' => [2_500_000, 'text-red-600'],
]);

it('uses brand colours for the lexpress.mu monthly sales comparison chart', function () {
    $user = User::factory()->create(['role' => UserRole::Admin]);
    Platform::factory()->create(['name' => 'lexpress.mu']);

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('#5e8ef4', false)
        ->assertSee('#b0e2f0', false);
});

it('uses brand colours for the 5plus.mu monthly sales comparison chart', function () {
    $user = User::factory()->create(['role' => UserRole::Admin]);
    Platform::factory()->create(['name' => '5plus.mu']);

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('#c84670', false)
        ->assertSee('#ffbb55', false);
});

it('leaves the yearly target neutral when no budget has been set', function () {
    $this->travelTo(Carbon::create(2026, 1, 1));

    $user = User::factory()->create(['role' => UserRole::Admin]);
    Platform::factory()->create(['name' => '5plus.mu']);

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('Achieved')
        ->assertDontSee('text-green-600', false)
        ->assertDontSee('text-amber-600', false)
        ->assertDontSee('text-red-600', false);
});

it('splits sales by placement into web and social media type', function () {
    $user = User::factory()->create(['role' => UserRole::Admin]);
    $fyStart = Budget::financialYearStartYear();
    $insideCurrentFy = Carbon::create($fyStart, Budget::FINANCIAL_YEAR_START_MONTH, 15);

    $platform = Platform::factory()->create(['name' => 'lexpress.mu']);
    $webPlacement = Placement::factory()->create([
        'platform_id' => $platform->id,
        'type' => PlacementType::Web,
    ]);
    $socialPlacement = Placement::factory()->create([
        'platform_id' => $platform->id,
        'type' => PlacementType::SocialMedia,
    ]);

    $webReservation = Reservation::factory()->create([
        'platform_id' => $platform->id,
        'placement_id' => $webPlacement->id,
        'gross_amount' => 40000,
    ]);
    $webReservation->created_at = $insideCurrentFy;
    $webReservation->saveQuietly();

    $socialReservation = Reservation::factory()->create([
        'platform_id' => $platform->id,
        'placement_id' => $socialPlacement->id,
        'gross_amount' => 10000,
    ]);
    $socialReservation->created_at = $insideCurrentFy;
    $socialReservation->saveQuietly();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertSee('Sales by Placement')
        ->assertSee('Web')
        ->assertSee('Social Media')
        ->assertSee('MUR '.number_format(40000))
        ->assertSee('MUR '.number_format(10000));
});
