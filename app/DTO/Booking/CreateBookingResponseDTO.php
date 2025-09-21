<?php

namespace App\DTO\Booking;

final class CreateBookingResponseDTO
{
    public function __construct(
        public readonly string $booking_uuid,
        public readonly string $reference,
        public readonly string $car_park,
        public readonly string $from,
        public readonly string $to,
        public readonly array $days,
        public readonly float $total_price,
        public readonly string $currency,
        public readonly string $status,
    ) {}

    public function toArray(): array
    {
        return [
            'booking_uuid' => $this->booking_uuid,
            'reference' => $this->reference,
            'car_park' => $this->car_park,
            'from' => $this->from,
            'to' => $this->to,
            'days' => $this->days,
            'total_price' => number_format($this->total_price, 2, '.', ''),
            'currency' => $this->currency,
            'status' => $this->status,
        ];
    }
}
