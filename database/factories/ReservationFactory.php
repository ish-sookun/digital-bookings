<?php

namespace Database\Factories;

use App\Models\Agency;
use App\Models\Client;
use App\Models\Placement;
use App\Models\Salesperson;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reservation>
 */
class ReservationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('now', '+1 month');
        $dates = [];
        for ($i = 0; $i < fake()->numberBetween(1, 7); $i++) {
            $dates[] = date('Y-m-d', strtotime("+{$i} days", $startDate->getTimestamp()));
        }

        return [
            'client_id' => Client::factory(),
            'agency_id' => fake()->optional()->passthrough(Agency::factory()),
            'salesperson_id' => fake()->optional()->passthrough(Salesperson::factory()),
            'product' => fake()->words(3, true),
            'placement_id' => Placement::factory(),
            'channel' => fake()->randomElement(['Run of site', 'Home & multimedia']),
            'scope' => fake()->randomElement(['Mauritius only', 'Worldwide']),
            'dates_booked' => $dates,
            'gross_amount' => fake()->randomFloat(2, 1000, 50000),
            'total_amount_to_pay' => fake()->randomFloat(2, 500, 49500),
            'discount' => fake()->randomFloat(2, 0, 500),
            'commission' => fake()->randomFloat(2, 0, 1000),
            'cost_of_artwork' => fake()->randomFloat(2, 0, 2000),
            'vat' => fake()->randomFloat(2, 0, 5000),
            'vat_exempt' => fake()->boolean(20),
            'purchase_order_no' => fake()->optional()->numerify('PO-####'),
            'invoice_no' => fake()->optional()->numerify('INV-####'),
            'remark' => fake()->optional()->sentence(),
        ];
    }
}
