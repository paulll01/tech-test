<?php

namespace Database\Factories;

use App\Models\CarParkModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PricingSeasonModel>
 */
class PricingSeasonModelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => Str::uuid(),
            'name' => $this->faker->randomElement(['Summer', 'Winter', 'Spring', 'Autumn']).' '.$this->faker->year,
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->modify('+2 days')->format('Y-m-d'),
            'weekday_price' => $this->faker->randomFloat(2, 20, 100),
            'weekend_price' => $this->faker->randomFloat(2, 25, 120),
            'car_park_id' => CarParkModel::factory(),
        ];
    }
}
