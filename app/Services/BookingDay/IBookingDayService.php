<?php

namespace App\Services\BookingDay;

use App\Models\CarParkModel;
use App\ValueObjects\Availability\DateRangeVO;

interface IBookingDayService
{
    /**
     * @return array<string,int>
     */
    public function getBookedPerDay(CarParkModel $carPark, DateRangeVO $range): array;
}
