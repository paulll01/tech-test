<?php

namespace App\DTO\Booking;

use App\Helpers\Booking\BookingOwnershipProof;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class CancelBookingDTO extends Data implements BookingOwnershipProof
{
    public function __construct(
        #[Required] public string $unique_reference,
        #[Required, Email] public string $customer_email,
        #[Required, Max(16)] public string $vehicle_reg,
    ) {}

    public function getUniqueReference(): string
    {
        return $this->unique_reference;
    }

    public function getCustomerEmail(): string
    {
        return $this->customer_email;
    }

    public function getVehicleReg(): string
    {
        return $this->vehicle_reg;
    }
}
