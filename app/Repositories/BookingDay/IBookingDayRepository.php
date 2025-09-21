<?php

namespace App\Repositories\BookingDay;

interface IBookingDayRepository
{
    /**
     * @param  string[]  $days
     * @return array<string,int>
     */
    public function countBookedPerDay(int $carParkId, array $days): array;
}
