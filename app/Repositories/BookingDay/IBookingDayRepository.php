<?php

namespace App\Repositories\BookingDay;

interface IBookingDayRepository
{
    /**
     * @param  string[]  $days
     * @return array<string,int>
     */
    public function countBookedPerDay(int $carParkId, array $days): array;

    /**
     * Returns overlapping days for a vehicle in a car park
     *
     * @param  array<string>  $days
     * @return array<string> overlap days
     */
    public function vehicleOverlaps(int $carParkId, string $vehicleReg, array $days): array;
}
