<?php

namespace App\Services\ApplicationServices\Booking;

use App\DTO\Booking\AmendBookingDTO;
use App\DTO\Booking\CreateBookingResponseDTO;
use App\Exceptions\Booking\BookingDatesUnchangedException;
use App\Exceptions\Booking\CannotAmendCancelledBookingException;
use App\Exceptions\Booking\CapacityExceededException;
use App\Exceptions\Booking\VehicleOverlapException;
use App\Models\BookingModel;
use App\Repositories\Booking\IBookingRepository;
use App\Repositories\BookingDay\IBookingDayRepository;
use App\Services\Booking\IBookingOwnershipService;
use App\Services\BookingDay\IBookingDayService;
use App\Services\Pricing\IQuotePricingService;
use App\ValueObjects\Availability\DateRangeVO;

class AmendBookingService implements IAmendBookingService
{
    public function __construct(
        private readonly IBookingDayService $bookingDayService,
        private readonly IBookingDayRepository $bookingDayRepo,
        private readonly IQuotePricingService $pricingService,
        private readonly IBookingRepository $bookingRepo,
        private readonly IBookingOwnershipService $ownership,
    ) {}

    public function handle(BookingModel $booking, AmendBookingDTO $data): CreateBookingResponseDTO
    {
        if ($booking->status === 'cancelled') {
            throw new CannotAmendCancelledBookingException;
        }

        // Ownership check
        $this->ownership->verify($booking, $data);

        $carPark = $booking->carPark;

        $newFrom = $data->from ?? $booking->from_date;
        $newTo = $data->to ?? $booking->to_date;

        // throw exception if dates unchanged
        if ($newFrom === $booking->from_date && $newTo === $booking->to_date) {
            throw new BookingDatesUnchangedException($booking->from_date, $booking->to_date);
        }

        $newRange = DateRangeVO::fromStrings($newFrom, $newTo);
        $newDays = $newRange->days();

        $oldRange = DateRangeVO::fromStrings($booking->from_date, $booking->to_date);
        $oldDays = $oldRange->days();

        $bookedMap = $this->bookingDayService->getBookedPerDay($carPark, $newRange);

        $blocking = [];
        foreach ($newDays as $d) {
            $booked = ($bookedMap[$d] ?? 0) - (in_array($d, $oldDays, true) ? 1 : 0);
            if ($booked >= $carPark->capacity) {
                $blocking[] = $d;
            }
        }
        if ($blocking) {
            throw new CapacityExceededException($blocking);
        }

        // excludes current booking days
        $overlaps = $this->bookingDayRepo->vehicleOverlaps(
            carParkId: $carPark->id,
            vehicleReg: $booking->vehicle_reg,
            days: $newDays
        );

        $overlaps = array_values(array_diff($overlaps, $oldDays));
        if ($overlaps) {
            throw new VehicleOverlapException($overlaps);
        }

        $quote = $this->pricingService->quote($carPark, $newRange);

        $updated = $this->bookingRepo->updateWithDays(
            booking: $booking,
            attributes: [
                'from_date' => $newFrom,
                'to_date' => $newTo,
                'total_price' => $quote->total,
            ],
            days: $newDays
        );

        // emit event Event::dispatch(new BookingAmendedEvent($props)) for email etc

        // eesponse
        $daysResponse = [];
        foreach ($newDays as $date) {
            $daysResponse[] = [
                'date' => $date,
                'price' => $quote->days[$date]['price'] ?? 0.0,
            ];
        }

        return new CreateBookingResponseDTO(
            booking_uuid: $updated->uuid,
            reference: $updated->unique_reference,
            car_park: $carPark->name,
            from: $newFrom,
            to: $newTo,
            days: $daysResponse,
            total_price: $quote->total,
            currency: $quote->currency,
            status: $updated->status,
        );
    }
}
