<?php

namespace Database\Factories;

use App\Models\CarParkModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CarParkModel>
 */
class CarParkModelFactory extends Factory
{
    protected $model = CarParkModel::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'name' => $this->faker->company().' Car Park',
            'capacity' => 10,
            'default_weekday_price' => $this->faker->randomFloat(2, 20, 80),
            'default_weekend_price' => $this->faker->randomFloat(2, 30, 100),
        ];
    }
}
