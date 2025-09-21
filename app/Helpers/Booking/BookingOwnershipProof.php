<?php

namespace App\Helpers\Booking;

interface BookingOwnershipProof
{
    public function getUniqueReference(): string;

    public function getCustomerEmail(): string;

    public function getVehicleReg(): string;
}
