<?php

namespace App\Repositories\PricingSeason;

use App\DTO\Pricing\SeasonDayPriceDTO;
use App\Models\CarParkModel;
use App\Models\PricingSeasonModel;
use App\ValueObjects\Availability\DateRangeVO;

class PricingSeasonRepository implements IPricingSeasonRepository
{
    /**
     * @return array<string, SeasonDayPriceDTO>
     */
    public function seasonByDate(CarParkModel $carPark, DateRangeVO $range): array
    {
        $from = $range->from->format('Y-m-d');
        $to = $range->to->format('Y-m-d');

        $seasons = PricingSeasonModel::query()
            ->where('car_park_id', $carPark->id)
            ->where(function ($q) use ($from, $to) {
                $q->whereBetween('start_date', [$from, $to])
                    ->orWhereBetween('end_date', [$from, $to])
                    ->orWhere(function ($covers) use ($from, $to) {
                        $covers->where('start_date', '<=', $from)
                            ->where('end_date', '>=', $to);
                    });
            })
            ->orderBy('start_date')
            ->get(['name', 'start_date', 'end_date', 'weekday_price', 'weekend_price']);

        if ($seasons->isEmpty()) {
            return [];
        }

        // assuming no overlaps between seasons
        $prepared = $seasons->map(fn ($s) => [
            'start' => new \DateTimeImmutable($s->start_date),
            'end' => new \DateTimeImmutable($s->end_date),
            'dto' => new SeasonDayPriceDTO(
                seasonName: $s->name,
                weekdayPrice: (float) $s->weekday_price,
                weekendPrice: (float) $s->weekend_price,
            ),
        ])->all();

        $pricesByDate = [];
        foreach ($range->days() as $day) {
            $date = new \DateTimeImmutable($day);
            foreach ($prepared as $season) {
                if ($date >= $season['start'] && $date <= $season['end']) {
                    $pricesByDate[$day] = $season['dto'];
                    break;
                }
            }
        }

        return $pricesByDate;
    }
}
