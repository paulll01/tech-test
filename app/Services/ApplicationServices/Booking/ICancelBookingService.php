<?php

namespace App\Services\ApplicationServices\Booking;

use App\DTO\Booking\CancelBookingDTO;
use App\DTO\Booking\CancelBookingResponseDTO;
use App\Models\BookingModel;

interface ICancelBookingService
{
    public function handle(BookingModel $booking, CancelBookingDTO $data): CancelBookingResponseDTO;
}
