<?php

namespace App\DTO\Pricing;

final class PricingQuoteDTO
{
    public function __construct(
        public readonly array $days,
        public readonly float $total,
        public readonly string $currency = 'GBP',
    ) {}

    public function toArray(): array
    {
        return [
            'days' => $this->days,
            'total' => number_format($this->total, 2, '.', ''),
            'currency' => $this->currency,
        ];
    }
}
