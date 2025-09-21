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

    public function vehicleOverlaps(int $carParkId, string $vehicleReg, array $days): array
    {
        if (empty($days)) {
            return [];
        }

        $rows = BookingDayModel::query()
            ->join('bookings', 'bookings.id', '=', 'booking_days.booking_id')
            ->where('booking_days.car_park_id', $carParkId)
            ->whereIn('booking_days.date', $days)
            ->where('bookings.vehicle_reg', strtoupper($vehicleReg))
            ->whereIn('bookings.status', ['confirmed', 'pending'])
            ->pluck('booking_days.date')
            ->unique()
            ->values()
            ->all();

        return $rows;
    }
}
