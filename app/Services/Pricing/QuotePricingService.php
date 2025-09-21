<?php

namespace App\Services\Pricing;

use App\DTO\Pricing\PricingQuoteDTO;
use App\Models\CarParkModel;
use App\Repositories\PricingSeason\IPricingSeasonRepository;
use App\ValueObjects\Availability\DateRangeVO;

class QuotePricingService implements IQuotePricingService
{
    public function __construct(
        private readonly IPricingSeasonRepository $seasons,
    ) {}

    public function quote(CarParkModel $carPark, DateRangeVO $range): PricingQuoteDTO
    {
        $days = $range->days();
        $seasonByDate = $this->seasons->seasonByDate($carPark, $range);
        $items = [];
        $total = 0.0;

        foreach ($days as $date) {
            $currentDay = new \DateTimeImmutable($date);
            $isWeekend = in_array($currentDay->format('N'), [6, 7]);

            $price = $isWeekend ? $carPark->default_weekend_price : $carPark->default_weekday_price;
            $source = 'default';

            // Check if a season price applies and override the default
            if (isset($seasonByDate[$date])) {
                $season = $seasonByDate[$date];
                $price = $isWeekend ? $season->weekendPrice : $season->weekdayPrice;
                $source = 'season:'.$season->seasonName;
            }

            $items[$date] = [
                'price' => round($price, 2),
                'source' => $source,
                'is_weekend' => $isWeekend,
            ];
            $total += $price;
        }

        return new PricingQuoteDTO($items, round($total, 2));
    }
}
