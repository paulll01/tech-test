<?php

namespace App\Services\Booking;

use App\Exceptions\Booking\OwnershipMismatchException;
use App\Helpers\Booking\BookingOwnershipProof;
use App\Models\BookingModel;

class BookingOwnershipService implements IBookingOwnershipService
{
    public function verify(BookingModel $booking, BookingOwnershipProof $proof): void
    {
        $refOk = $booking->unique_reference === $proof->getUniqueReference();
        $emailOk = strcasecmp($booking->customer_email, $proof->getCustomerEmail()) === 0;
        $regOk = strtoupper($booking->vehicle_reg) === strtoupper($proof->getVehicleReg());

        if (! ($refOk && $emailOk && $regOk)) {
            throw new OwnershipMismatchException($refOk, $emailOk, $regOk);
        }
    }
}
