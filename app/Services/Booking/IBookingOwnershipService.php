<?php

namespace App\Services\Booking;

use App\Helpers\Booking\BookingOwnershipProof;
use App\Models\BookingModel;

interface IBookingOwnershipService
{
    public function verify(BookingModel $booking, BookingOwnershipProof $proof): void;
}
