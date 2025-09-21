<?php

namespace App\Services\Pricing;

use App\DTO\Pricing\PricingQuoteDTO;
use App\Models\CarParkModel;
use App\ValueObjects\Availability\DateRangeVO;

interface IQuotePricingService
{
    public function quote(CarParkModel $carPark, DateRangeVO $range): PricingQuoteDTO;
}
