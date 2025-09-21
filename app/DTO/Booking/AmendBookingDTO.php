<?php

namespace App\DTO\Booking;

use App\Helpers\Booking\BookingOwnershipProof;
use Spatie\LaravelData\Attributes\Validation\AfterOrEqual;
use Spatie\LaravelData\Attributes\Validation\BeforeOrEqual;
use Spatie\LaravelData\Attributes\Validation\Date;
use Spatie\LaravelData\Attributes\Validation\DateFormat;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Nullable;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Attributes\Validation\Sometimes;
use Spatie\LaravelData\Data;

class AmendBookingDTO extends Data implements BookingOwnershipProof
{
    public function __construct(
        #[Sometimes, Nullable, Date, AfterOrEqual('today'), BeforeOrEqual('to'), DateFormat('Y-m-d')]
        public ?string $from,

        #[Sometimes, Nullable, Date, AfterOrEqual('from'), DateFormat('Y-m-d')]
        public ?string $to,

        #[Required, Max(7)]
        public ?string $vehicle_reg,

        #[Required, Email]
        public ?string $customer_email,

        #[Required]
        public ?string $unique_reference,
    ) {}

    public function getUniqueReference(): string
    {
        return (string) $this->unique_reference;
    }

    public function getCustomerEmail(): string
    {
        return (string) $this->customer_email;
    }

    public function getVehicleReg(): string
    {
        return (string) $this->vehicle_reg;
    }
}
