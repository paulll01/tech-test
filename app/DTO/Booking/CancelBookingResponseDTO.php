<?php

namespace App\DTO\Booking;

final class CancelBookingResponseDTO
{
    public function __construct(
        public readonly string $booking_uuid,
        public readonly string $reference,
        public readonly string $status,
        public readonly string $cancelled_at,
    ) {}

    public function toArray(): array
    {
        return [
            'booking_uuid' => $this->booking_uuid,
            'reference' => $this->reference,
            'status' => $this->status,
            'cancelled_at' => $this->cancelled_at,
        ];
    }
}
