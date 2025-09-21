<?php

namespace App\Repositories\PricingSeason;

use App\DTO\Pricing\SeasonDayPriceDTO;
use App\Models\CarParkModel;
use App\ValueObjects\Availability\DateRangeVO;

interface IPricingSeasonRepository
{
    /**
     * @return array<string,SeasonDayPriceDTO>
     */
    public function seasonByDate(CarParkModel $carPark, DateRangeVO $range): array;
}
