<?php

namespace App\Services\ApplicationServices\Availability;

use App\DTO\Availability\CheckAvailabilityDTO;
use App\DTO\Availability\CheckAvailabilityResponseDTO;
use App\Models\CarParkModel;
use App\Services\BookingDay\IBookingDayService;
use App\Services\Pricing\IQuotePricingService;
use App\ValueObjects\Availability\DateRangeVO;

class CheckAvailabilityService implements ICheckAvailabilityService
{
    public function __construct(
        private IBookingDayService $bookingDayService,
        private IQuotePricingService $pricingService,
    ) {}

    public function handle(CarParkModel $carPark, CheckAvailabilityDTO $data): CheckAvailabilityResponseDTO
    {
        $range = DateRangeVO::fromStrings($data->from, $data->to);
        $days = $range->days();

        // Get how many bookings exist per day in the requested range
        $bookedMap = $this->bookingDayService->getBookedPerDay($carPark, $range);

        // Get pricing for the requested range by days
        $quote = $this->pricingService->quote($carPark, $range);

        $priceByDate = $quote->days;

        $resultDays = [];
        $capacity = $carPark->capacity;
        $available = true;

        foreach ($days as $day) {
            $booked = $bookedMap[$day] ?? 0;
            $spacesAvailable = max(0, $capacity - $booked);

            $resultDays[] = [
                'date' => $day,
                'available' => $spacesAvailable,
                'price' => $priceByDate[$day]['price'],
                'is_weekend' => $priceByDate[$day]['is_weekend'] ?? false,
                'price_source' => $priceByDate[$day]['source'] ?? 'default',
            ];

            if ($spacesAvailable === 0) {
                $available = false;
            }
        }

        return new CheckAvailabilityResponseDTO(
            carParkName: $carPark->name,
            capacity: $capacity,
            days: $resultDays,
            available: $available,
            totalPrice: $quote->total,
            currency: $quote->currency,
        );
    }
}
