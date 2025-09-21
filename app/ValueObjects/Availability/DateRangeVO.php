<?php

namespace App\ValueObjects\Availability;

use DateInterval;
use DatePeriod;
use DateTimeImmutable;
use InvalidArgumentException;

final class DateRangeVO
{
    public function __construct(
        public readonly DateTimeImmutable $from,
        public readonly DateTimeImmutable $to
    ) {
        if ($this->from > $this->to) {
            throw new InvalidArgumentException('from must be <= to');
        }

        if ($this->from->format('H:i:s') !== '00:00:00' || $this->to->format('H:i:s') !== '00:00:00') {
            throw new InvalidArgumentException('DateRange expects date-only (no time).');
        }
    }

    /** @return string[] */
    public function days(): array
    {
        $period = new DatePeriod(
            $this->from,
            new DateInterval('P1D'),
            $this->to->modify('+1 day')
        );

        return array_map(
            fn ($d) => $d->format('Y-m-d'),
            iterator_to_array($period)
        );
    }

    public static function fromStrings(string $from, string $to): self
    {
        return new self(
            new DateTimeImmutable($from.' 00:00:00'),
            new DateTimeImmutable($to.' 00:00:00'),
        );
    }
}
