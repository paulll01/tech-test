<?php

namespace App\Services\ApplicationServices\Booking;

use App\DTO\Booking\AmendBookingDTO;
use App\DTO\Booking\CreateBookingResponseDTO;
use App\Models\BookingModel;

interface IAmendBookingService
{
    public function handle(BookingModel $booking, AmendBookingDTO $data): CreateBookingResponseDTO;
}
