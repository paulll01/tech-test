<?php

namespace App\DTO\Pricing;

final class SeasonDayPriceDTO
{
    public function __construct(
        public readonly string $seasonName,
        public readonly float $weekdayPrice,
        public readonly float $weekendPrice,
    ) {}
}
