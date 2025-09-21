<?php

namespace App\Services\ApplicationServices\Availability;

use App\DTO\Availability\CheckAvailabilityDTO;
use App\DTO\Availability\CheckAvailabilityResponseDTO;
use App\Models\CarParkModel;

interface ICheckAvailabilityService
{
    public function handle(CarParkModel $carPark, CheckAvailabilityDTO $data): CheckAvailabilityResponseDTO;
}
