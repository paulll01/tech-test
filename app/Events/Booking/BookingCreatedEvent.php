<?php

namespace App\Events\Booking;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BookingCreatedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly string $bookingUuid,
        public readonly int $carParkId,
        public readonly string $from,
        public readonly string $to,
        /** @var string[] */
        public readonly array $days,
        public readonly float $total,
        public readonly string $currency,
        public readonly string $vehicleReg,
        public readonly string $customerEmail,
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}
