<?php

namespace App\Services\ApplicationServices\Booking;

use App\DTO\Booking\CreateBookingDTO;
use App\DTO\Booking\CreateBookingResponseDTO;
use App\Models\CarParkModel;

interface ICreateBookingService
{
    public function handle(CarParkModel $carPark, CreateBookingDTO $data): CreateBookingResponseDTO;
}
