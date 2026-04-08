<?php

namespace Database\Factories;

use App\PlacementType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Placement>
 */
class PlacementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'description' => fake()->optional()->paragraph(),
            'type' => fake()->randomElement(PlacementType::cases()),
            'price' => fake()->numberBetween(1000, 100000),
        ];
    }
}
