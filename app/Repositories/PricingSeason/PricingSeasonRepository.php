<?php

namespace App\Repositories\PricingSeason;

use App\DTO\Pricing\SeasonDayPriceDTO;
use App\Models\CarParkModel;
use App\Models\PricingSeasonModel;
use App\ValueObjects\Availability\DateRangeVO;

final class PricingSeasonRepository implements IPricingSeasonRepository
{
    /**
     * @return array<string, SeasonDayPriceDTO>
     */
    public function seasonByDate(CarParkModel $carPark, DateRangeVO $range): array
    {
        $from = $range->from->format('Y-m-d');
        $to = $range->to->format('Y-m-d');

        $overlappingSeasons = PricingSeasonModel::query()
            ->where('car_park_id', $carPark->id)
            ->where(function ($query) use ($from, $to) {
                $query->whereBetween('start_date', [$from, $to])
                    ->orWhereBetween('end_date', [$from, $to])
                    ->orWhere(function ($covers) use ($from, $to) {
                        $covers->where('start_date', '<=', $from)
                            ->where('end_date', '>=', $to);
                    });
            })
            ->orderBy('start_date')
            ->get();

        if ($overlappingSeasons->isEmpty()) {
            return [];
        }

        $pricesByDate = [];

        foreach ($overlappingSeasons as $season) {
            $seasonStartDate = new \DateTimeImmutable($season->start_date);
            $seasonEndDate = new \DateTimeImmutable($season->end_date);

            for ($cursorDate = $seasonStartDate; $cursorDate <= $seasonEndDate; $cursorDate = $cursorDate->modify('+1 day')) {
                $key = $cursorDate->format('Y-m-d');

                if (! array_key_exists($key, $pricesByDate)) {
                    $pricesByDate[$key] = new SeasonDayPriceDTO(
                        seasonName: $season->name,
                        weekdayPrice: (float) $season->weekday_price,
                        weekendPrice: (float) $season->weekend_price,
                    );
                }
            }
        }

        return $pricesByDate;
    }
}
