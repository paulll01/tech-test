<?php

namespace App\Repositories\BookingDay;

use App\Models\BookingDayModel;

class BookingDayRepository implements IBookingDayRepository
{
    public function countBookedPerDay(int $carParkId, array $days): array
    {
        if (empty($days)) {
            return [];
        }

        return BookingDayModel::query()
            ->selectRaw('date, COUNT(*) as booked')
            ->where('car_park_id', $carParkId)
            ->whereIn('date', $days)
            ->groupBy('date')
            ->pluck('booked', 'date')
            ->all();
    }
}
