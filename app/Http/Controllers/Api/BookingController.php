<?php

namespace App\Http\Controllers\Api;

use App\DTO\Booking\AmendBookingDTO;
use App\DTO\Booking\CancelBookingDTO;
use App\DTO\Booking\CreateBookingDTO;
use App\Http\Controllers\Controller;
use App\Models\BookingModel;
use App\Models\CarParkModel;
use App\Services\ApplicationServices\Booking\IAmendBookingService;
use App\Services\ApplicationServices\Booking\ICancelBookingService;
use App\Services\ApplicationServices\Booking\ICreateBookingService;
use Illuminate\Http\JsonResponse;

class BookingController extends Controller
{
    public function __construct(
        private ICreateBookingService $service,
        private IAmendBookingService $amendBookingService,
        private ICancelBookingService $cancelBookingService
    ) {}

    public function store(CarParkModel $carPark, CreateBookingDTO $data): JsonResponse
    {
        $result = $this->service->handle($carPark, $data);

        return response()->json($result->toArray(), 201);
    }

    public function update(BookingModel $booking, AmendBookingDTO $data): JsonResponse
    {
        $result = $this->amendBookingService->handle($booking, $data);

        return response()->json($result->toArray(), 200);
    }

    public function cancel(BookingModel $booking, CancelBookingDTO $data): JsonResponse
    {
        $result = $this->cancelBookingService->handle($booking, $data);

        return response()->json($result->toArray(), 200);
    }
}
