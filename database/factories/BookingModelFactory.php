<?php

namespace Database\Factories;

use App\Models\BookingModel;
use App\Models\CarParkModel;
use App\Models\PricingSeasonModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BookingModel>
 */
class BookingModelFactory extends Factory
{
    protected $model = BookingModel::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $from = $this->faker->dateTimeBetween('now', '+1 month');
        $to = (clone $from)->modify('+'.$this->faker->numberBetween(1, 14).' days');

        return [
            'uuid' => (string) Str::uuid(),
            'car_park_id' => CarParkModel::factory(),
            'customer_email' => $this->faker->safeEmail(),
            'vehicle_reg' => strtoupper($this->faker->bothify('??##???')),
            'from_date' => $from->format('Y-m-d'),
            'to_date' => $to->format('Y-m-d'),
            'status' => 'confirmed',
            'total_price' => 0,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (BookingModel $booking) {
            $dates = $this->expandDates(
                $booking->from_date,
                $booking->to_date
            );

            $rows = array_map(fn (string $date) => [
                'car_park_id' => $booking->car_park_id,
                'date' => $date,
            ], $dates);

            $booking->bookingDays()->createMany($rows);

            $year = (int) now()->format('Y');
            $seasonStart = (new \DateTimeImmutable("$year-06-01"))->format('Y-m-d');
            $seasonEnd = (new \DateTimeImmutable($booking->from_date))->modify('+2 days')->format('Y-m-d');
            $seasonName = "Summer $year";

            PricingSeasonModel::factory()->create(
                [
                    'car_park_id' => $booking->car_park_id,
                    'name' => $seasonName,
                    'start_date' => $seasonStart,
                    'end_date' => $seasonEnd,
                    'weekday_price' => 35.00,
                    'weekend_price' => 50.00,
                ]
            );

            $overlappingSeasons = PricingSeasonModel::query()
                ->where('car_park_id', $booking->car_park_id)
                ->where(function ($q) use ($booking) {
                    $from = $booking->from_date;
                    $to = $booking->to_date;
                    $q->whereBetween('start_date', [$from, $to])
                        ->orWhereBetween('end_date', [$from, $to])
                        ->orWhere(function ($covers) use ($from, $to) {
                            $covers->where('start_date', '<=', $from)
                                ->where('end_date', '>=', $to);
                        });
                })
                ->orderBy('start_date')
                ->get();

            $seasonPriceByDate = [];
            foreach ($overlappingSeasons as $s) {
                $sStart = new \DateTimeImmutable($s->start_date);
                $sEnd = new \DateTimeImmutable($s->end_date);
                for ($d = $sStart; $d <= $sEnd; $d = $d->modify('+1 day')) {
                    $key = $d->format('Y-m-d');
                    if (! isset($seasonPriceByDate[$key])) {
                        $seasonPriceByDate[$key] = [
                            'weekday' => (float) $s->weekday_price,
                            'weekend' => (float) $s->weekend_price,
                        ];
                    }
                }
            }

            $carPark = $booking->carPark;
            $total = 0.0;

            foreach ($dates as $d) {
                $dow = (int) (new \DateTimeImmutable($d))->format('N');
                $isWeekend = ($dow >= 6);

                if (isset($seasonPriceByDate[$d])) {
                    $price = $isWeekend
                        ? $seasonPriceByDate[$d]['weekend']
                        : $seasonPriceByDate[$d]['weekday'];
                } else {
                    $price = $isWeekend
                        ? (float) $carPark->default_weekend_price
                        : (float) $carPark->default_weekday_price;
                }

                $total += $price;
            }

            $booking->update(['total_price' => round($total, 2)]);
        });
    }

    /**
     * @return array<int,string>
     */
    private function expandDates(string $from, string $to): array
    {
        $start = new \DateTimeImmutable($from);
        $end = new \DateTimeImmutable($to);

        $out = [];
        for ($d = $start; $d <= $end; $d = $d->modify('+1 day')) {
            $out[] = $d->format('Y-m-d');
        }

        return $out;
    }
}
