<?php

namespace App\Repositories\Booking;

use App\Models\BookingModel;

interface IBookingRepository
{
    /**
     * @param  array<string,mixed>  $bookingAttributes
     * @param  array<string>  $days
     */
    public function createWithDays(array $bookingAttributes, array $days): BookingModel;

    public function updateWithDays(BookingModel $booking, array $attributes, array $days): BookingModel;

    public function cancelAndReleaseDays(BookingModel $booking): BookingModel;
}
