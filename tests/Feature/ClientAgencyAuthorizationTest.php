<?php

use App\CommissionType;
use App\DiscountType;
use App\Models\Agency;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('client access', function () {
    it('lets salespeople view the clients index', function () {
        $user = User::factory()->salesperson()->create();
        Client::factory()->create(['company_name' => 'Acme Industries Ltd']);

        $this->actingAs($user)
            ->get(route('clients.index'))
            ->assertOk()
            ->assertSee('Acme Industries Ltd');
    });

    it('lets salespeople reach the create form without the commission section', function () {
        $user = User::factory()->salesperson()->create();

        $this->actingAs($user)
            ->get(route('clients.create'))
            ->assertOk()
            ->assertDontSee('Commission')
            ->assertDontSee('Discount Amount');
    });

    it('lets salespeople reach the edit form without the commission section', function () {
        $user = User::factory()->salesperson()->create();
        $client = Client::factory()->create();

        $this->actingAs($user)
            ->get(route('clients.edit', $client))
            ->assertOk()
            ->assertDontSee('Commission')
            ->assertDontSee('Discount Amount');
    });

    it('shows the commission section to admins on the create form', function () {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('clients.create'))
            ->assertOk()
            ->assertSee('Commission')
            ->assertSee('Discount Amount');
    });

    it('lets salespeople create a client without commission or discount', function () {
        $user = User::factory()->salesperson()->create();

        $this->actingAs($user)
            ->post(route('clients.store'), [
                'company_name' => 'New Client Ltd',
                'brn' => 'C12345678',
                'phone' => '1234567',
                'address' => '123 Main St',
            ])
            ->assertRedirect(route('clients.index'));

        $client = Client::where('company_name', 'New Client Ltd')->firstOrFail();
        expect($client->commission_amount)->toBeNull();
        expect($client->discount)->toBeNull();
    });

    it('strips commission and discount from salesperson create requests', function () {
        $user = User::factory()->salesperson()->create();

        $this->actingAs($user)
            ->post(route('clients.store'), [
                'company_name' => 'Sneaky Client',
                'brn' => 'C87654321',
                'phone' => '7654321',
                'address' => '456 Side St',
                'commission_amount' => 50,
                'commission_type' => CommissionType::Percentage->value,
                'discount' => 30,
                'discount_type' => DiscountType::Percentage->value,
            ])
            ->assertRedirect(route('clients.index'));

        $client = Client::where('company_name', 'Sneaky Client')->firstOrFail();
        expect($client->commission_amount)->toBeNull();
        expect($client->commission_type)->toBeNull();
        expect($client->discount)->toBeNull();
        expect($client->discount_type)->toBeNull();
    });

    it('preserves existing commission when a salesperson updates a client', function () {
        $user = User::factory()->salesperson()->create();
        $client = Client::factory()->create([
            'commission_amount' => 15,
            'commission_type' => CommissionType::Percentage,
            'discount' => 10,
            'discount_type' => DiscountType::Percentage,
        ]);

        $this->actingAs($user)
            ->put(route('clients.update', $client), [
                'company_name' => 'Updated Name Ltd',
                'brn' => $client->brn,
                'phone' => $client->phone,
                'address' => $client->address,
                'commission_amount' => 99,
                'discount' => 99,
            ])
            ->assertRedirect(route('clients.index'));

        $client->refresh();
        expect($client->company_name)->toBe('Updated Name Ltd');
        expect($client->commission_amount)->toBe(15);
        expect($client->commission_type)->toBe(CommissionType::Percentage);
        expect($client->discount)->toBe(10);
        expect($client->discount_type)->toBe(DiscountType::Percentage);
    });

    it('lets admins update commission and discount', function () {
        $admin = User::factory()->admin()->create();
        $client = Client::factory()->create([
            'commission_amount' => 10,
            'discount' => 5,
        ]);

        $this->actingAs($admin)
            ->put(route('clients.update', $client), [
                'company_name' => $client->company_name,
                'brn' => $client->brn,
                'phone' => $client->phone,
                'address' => $client->address,
                'commission_amount' => 50,
                'commission_type' => CommissionType::Percentage->value,
                'discount' => 25,
                'discount_type' => DiscountType::Percentage->value,
            ])
            ->assertRedirect(route('clients.index'));

        $client->refresh();
        expect($client->commission_amount)->toBe(50);
        expect($client->discount)->toBe(25);
    });

    it('forbids salespeople from deleting clients', function () {
        $user = User::factory()->salesperson()->create();
        $client = Client::factory()->create();

        $this->actingAs($user)
            ->delete(route('clients.destroy', $client))
            ->assertForbidden();

        expect(Client::find($client->id))->not->toBeNull();
    });

    it('lets admins delete clients', function () {
        $admin = User::factory()->admin()->create();
        $client = Client::factory()->create();

        $this->actingAs($admin)
            ->delete(route('clients.destroy', $client))
            ->assertRedirect(route('clients.index'));

        expect(Client::find($client->id))->toBeNull();
    });
});

describe('agency access', function () {
    it('lets salespeople view the agencies index', function () {
        $user = User::factory()->salesperson()->create();
        Agency::factory()->create(['company_name' => 'Initech Media']);

        $this->actingAs($user)
            ->get(route('agencies.index'))
            ->assertOk()
            ->assertSee('Initech Media');
    });

    it('lets salespeople reach the create form without the commission section', function () {
        $user = User::factory()->salesperson()->create();

        $this->actingAs($user)
            ->get(route('agencies.create'))
            ->assertOk()
            ->assertDontSee('Commission')
            ->assertDontSee('Discount Amount');
    });

    it('lets salespeople reach the edit form without the commission section', function () {
        $user = User::factory()->salesperson()->create();
        $agency = Agency::factory()->create();

        $this->actingAs($user)
            ->get(route('agencies.edit', $agency))
            ->assertOk()
            ->assertDontSee('Commission')
            ->assertDontSee('Discount Amount');
    });

    it('strips commission and discount from salesperson create requests', function () {
        $user = User::factory()->salesperson()->create();

        $this->actingAs($user)
            ->post(route('agencies.store'), [
                'company_name' => 'Sneaky Agency',
                'brn' => 'A87654321',
                'phone' => '7654321',
                'address' => '789 Corner St',
                'commission_amount' => 50,
                'commission_type' => CommissionType::Percentage->value,
                'discount' => 30,
                'discount_type' => DiscountType::Percentage->value,
            ])
            ->assertRedirect(route('agencies.index'));

        $agency = Agency::where('company_name', 'Sneaky Agency')->firstOrFail();
        expect($agency->commission_amount)->toBeNull();
        expect($agency->commission_type)->toBeNull();
        expect($agency->discount)->toBeNull();
        expect($agency->discount_type)->toBeNull();
    });

    it('preserves existing commission when a salesperson updates an agency', function () {
        $user = User::factory()->salesperson()->create();
        $agency = Agency::factory()->create([
            'commission_amount' => 20,
            'commission_type' => CommissionType::Percentage,
            'discount' => 15,
            'discount_type' => DiscountType::Percentage,
        ]);

        $this->actingAs($user)
            ->put(route('agencies.update', $agency), [
                'company_name' => 'Updated Agency Name',
                'brn' => $agency->brn,
                'phone' => $agency->phone,
                'address' => $agency->address,
                'commission_amount' => 99,
                'discount' => 99,
            ])
            ->assertRedirect(route('agencies.index'));

        $agency->refresh();
        expect($agency->company_name)->toBe('Updated Agency Name');
        expect($agency->commission_amount)->toBe(20);
        expect($agency->commission_type)->toBe(CommissionType::Percentage);
        expect($agency->discount)->toBe(15);
        expect($agency->discount_type)->toBe(DiscountType::Percentage);
    });

    it('lets admins update commission and discount', function () {
        $admin = User::factory()->admin()->create();
        $agency = Agency::factory()->create([
            'commission_amount' => 10,
            'discount' => 5,
        ]);

        $this->actingAs($admin)
            ->put(route('agencies.update', $agency), [
                'company_name' => $agency->company_name,
                'brn' => $agency->brn,
                'phone' => $agency->phone,
                'address' => $agency->address,
                'commission_amount' => 40,
                'commission_type' => CommissionType::Percentage->value,
                'discount' => 20,
                'discount_type' => DiscountType::Percentage->value,
            ])
            ->assertRedirect(route('agencies.index'));

        $agency->refresh();
        expect($agency->commission_amount)->toBe(40);
        expect($agency->discount)->toBe(20);
    });

    it('forbids salespeople from deleting agencies', function () {
        $user = User::factory()->salesperson()->create();
        $agency = Agency::factory()->create();

        $this->actingAs($user)
            ->delete(route('agencies.destroy', $agency))
            ->assertForbidden();

        expect(Agency::find($agency->id))->not->toBeNull();
    });

    it('lets admins delete agencies', function () {
        $admin = User::factory()->admin()->create();
        $agency = Agency::factory()->create();

        $this->actingAs($admin)
            ->delete(route('agencies.destroy', $agency))
            ->assertRedirect(route('agencies.index'));

        expect(Agency::find($agency->id))->toBeNull();
    });
});
