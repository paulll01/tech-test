<?php

namespace App\DTO\Availability;

use Spatie\LaravelData\Attributes\Validation\Date;
use Spatie\LaravelData\Attributes\Validation\Required;
use Spatie\LaravelData\Data;

class CheckAvailabilityDTO extends Data
{
    public function __construct(
        #[Required, Date]
        public string $from,

        #[Required, Date]
        public string $to,
    ) {}
}
