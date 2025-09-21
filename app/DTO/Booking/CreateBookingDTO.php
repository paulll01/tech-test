<?php

namespace App\DTO\Booking;

use Spatie\LaravelData\Attributes\Validation\AfterOrEqual;
use Spatie\LaravelData\Attributes\Validation\BeforeOrEqual;
use Spatie\LaravelData\Attributes\Validation\Date;
use Spatie\LaravelData\Attributes\Validation\DateFormat;
use Spatie\LaravelData\Attributes\Validation\Email;
use Spatie\LaravelData\Attributes\Validation\Max;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class CreateBookingDTO extends Data
{
    public function __construct(
        #[Required, Date, BeforeOrEqual('to'), AfterOrEqual('today'), DateFormat('Y-m-d')]
        public string $from,

        #[Required, Date, DateFormat('Y-m-d'), AfterOrEqual('from')]
        public string $to,

        #[Required, Email]
        public string $customer_email,

        #[Required, Max(16)]
        public string $vehicle_reg,
    ) {}
}
