<?php

namespace Database\Factories;

use App\CommissionType;
use App\DiscountType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_name' => fake()->company(),
            'brn' => fake()->unique()->numerify('C########'),
            'vat_number' => fake()->optional()->randomNumber(8),
            'vat_exempt' => fake()->boolean(20),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'commission_amount' => fake()->optional()->numberBetween(1, 100),
            'commission_type' => fake()->optional()->randomElement(CommissionType::cases()),
            'discount' => fake()->optional()->numberBetween(1, 100),
            'discount_type' => fake()->optional()->randomElement(DiscountType::cases()),
            'contact_person_name' => fake()->optional()->name(),
            'contact_person_email' => fake()->optional()->safeEmail(),
            'contact_person_phone' => fake()->optional()->phoneNumber(),
        ];
    }
}
