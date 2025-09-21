<?php

namespace App\Services\BookingDay;

use App\Models\CarParkModel;
use App\Repositories\BookingDay\IBookingDayRepository;
use App\ValueObjects\Availability\DateRangeVO;

class BookingDayService implements IBookingDayService
{
    public function __construct(
        private readonly IBookingDayRepository $bookingDaysRepository
    ) {}

    public function getBookedPerDay(CarParkModel $carPark, DateRangeVO $range): array
    {
        return $this->bookingDaysRepository->countBookedPerDay($carPark->id, $range->days());
    }
}
