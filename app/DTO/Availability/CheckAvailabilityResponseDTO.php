<?php

namespace App\DTO\Availability;

final class CheckAvailabilityResponseDTO
{
    public function __construct(
        public readonly string $carParkName,
        public readonly int $capacity,
        public readonly array $days,
        public readonly bool $available,
        public readonly float $totalPrice,
        public readonly string $currency = 'GBP',
    ) {}

    public function toArray(): array
    {
        return [
            'car_park' => $this->carParkName,
            'capacity' => $this->capacity,
            'days' => $this->days,
            'availability' => $this->available,
            'total_price' => number_format($this->totalPrice, 2, '.', ''),
            'currency' => $this->currency,
        ];
    }
}
