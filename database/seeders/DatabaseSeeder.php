<?php

namespace Database\Seeders;

use App\Models\BookingModel;
use App\Models\CarParkModel;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        /** @var \App\Models\CarParkModel $carPark */
        $carPark = CarParkModel::factory()->create([
            'name' => 'T1',
            'capacity' => 10,
            'default_weekday_price' => 30.00,
            'default_weekend_price' => 40.00,
        ]);

        $from = now()->addDay()->startOfDay()->toDateString();
        $to = now()->addDays(5)->startOfDay()->toDateString();

        BookingModel::factory()
            ->create([
                'car_park_id' => $carPark->id,
                'from_date' => $from,
                'to_date' => $to,
                'status' => 'confirmed',
            ]);
    }
}
