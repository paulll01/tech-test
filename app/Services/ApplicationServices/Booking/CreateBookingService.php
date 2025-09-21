<?php

namespace App\Services\ApplicationServices\Booking;

use App\DTO\Booking\CreateBookingDTO;
use App\DTO\Booking\CreateBookingResponseDTO;
use App\Events\Booking\BookingCreatedEvent;
use App\Exceptions\Booking\CapacityExceededException;
use App\Exceptions\Booking\VehicleOverlapException;
use App\Models\CarParkModel;
use App\Repositories\Booking\IBookingRepository;
use App\Repositories\BookingDay\IBookingDayRepository;
use App\Services\BookingDay\IBookingDayService;
use App\Services\Pricing\IQuotePricingService;
use App\ValueObjects\Availability\DateRangeVO;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

class CreateBookingService implements ICreateBookingService
{
    public function __construct(
        private readonly IBookingDayService $bookingDayService,
        private readonly IBookingDayRepository $bookingDayRepo,
        private readonly IQuotePricingService $pricingService,
        private readonly IBookingRepository $bookingRepo,
    ) {}

    public function handle(CarParkModel $carPark, CreateBookingDTO $data): CreateBookingResponseDTO
    {
        $range = DateRangeVO::fromStrings($data->from, $data->to);
        $days = $range->days();

        $bookedMap = $this->bookingDayService->getBookedPerDay($carPark, $range);
        $blocking = [];

        // forbidcreation of bookings if any day is full
        foreach ($days as $day) {
            $booked = $bookedMap[$day] ?? 0;
            if ($booked >= $carPark->capacity) {
                $blocking[] = $day;
            }
        }
        if (! empty($blocking)) {
            throw new CapacityExceededException($blocking);
        }

        // forbid creation of overlapping bookings for same reg
        $overlaps = $this->bookingDayRepo->vehicleOverlaps(
            carParkId: $carPark->id,
            vehicleReg: $data->vehicle_reg,
            days: $days
        );

        if ($overlaps) {
            throw new VehicleOverlapException($overlaps);
        }

        $quote = $this->pricingService->quote($carPark, $range);

        // create booking + booking_days
        $booking = $this->bookingRepo->createWithDays([
            'uuid' => (string) Str::uuid(),
            'car_park_id' => $carPark->id,
            'customer_email' => $data->customer_email,
            'vehicle_reg' => strtoupper($data->vehicle_reg),
            'from_date' => $data->from,
            'to_date' => $data->to,
            'status' => 'pending',
            'total_price' => $quote->total,
        ], $days);

        // fire event for further processing (emaail,etc)
        Event::dispatch(new BookingCreatedEvent(
            bookingUuid: $booking->uuid,
            carParkId: $booking->car_park_id,
            from: $data->from,
            to: $data->to,
            days: $days,
            total: $quote->total,
            currency: $quote->currency,
            vehicleReg: $booking->vehicle_reg,
            customerEmail: $booking->customer_email,
        ));

        $daysResponse = [];
        foreach ($days as $date) {
            $daysResponse[] = [
                'date' => $date,
                'price' => $quote->days[$date]['price'] ?? 0.0,
            ];
        }

        return new CreateBookingResponseDTO(
            booking_uuid: $booking->uuid,
            reference: $booking->unique_reference,
            car_park: $carPark->name,
            from: $data->from,
            to: $data->to,
            days: $daysResponse,
            total_price: $quote->total,
            currency: $quote->currency,
            status: $booking->status,
        );
    }
}
