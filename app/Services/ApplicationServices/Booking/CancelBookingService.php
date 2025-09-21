<?php

namespace App\Services\ApplicationServices\Booking;

use App\DTO\Booking\CancelBookingDTO;
use App\DTO\Booking\CancelBookingResponseDTO;
use App\Events\Booking\BookingCancelledEvent;
use App\Exceptions\Booking\CannotCancelCancelledBookingException;
use App\Models\BookingModel;
use App\Repositories\Booking\IBookingRepository;
use App\Services\Booking\IBookingOwnershipService;
use App\ValueObjects\Availability\DateRangeVO;

class CancelBookingService implements ICancelBookingService
{
    public function __construct(
        private readonly IBookingRepository $bookingRepo,
        private readonly IBookingOwnershipService $ownership,
    ) {}

    public function handle(BookingModel $booking, CancelBookingDTO $data): CancelBookingResponseDTO
    {
        if ($booking->status === 'cancelled') {
            throw new CannotCancelCancelledBookingException;
        }

        // Ownership check
        $this->ownership->verify($booking, $data);

        $range = DateRangeVO::fromStrings($booking->from_date, $booking->to_date);
        $days = $range->days();

        // cancel and delete days
        $updated = $this->bookingRepo->cancelAndReleaseDays($booking);

        $cancelledAt = now()->toIso8601String();

        // emit event Event::dispatch(new BookingCancelledEvent($props)) for email etc

        return new CancelBookingResponseDTO(
            booking_uuid: $updated->uuid,
            reference: $updated->unique_reference,
            status: $updated->status,
            cancelled_at: $cancelledAt,
        );
    }
}
