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
use OpenApi\Attributes as OA;

class BookingController extends Controller
{
    public function __construct(
        private ICreateBookingService $service,
        private IAmendBookingService $amendBookingService,
        private ICancelBookingService $cancelBookingService
    ) {}

    #[OA\Post(
        path: '/car-parks/{carPark}/bookings',
        operationId: 'createBooking',
        summary: 'Create a booking for a given car park',
        description: 'Creates a booking in the given range. Validates overlap and capacity',
        tags: ['Bookings'],
        parameters: [
            new OA\Parameter(
                name: 'carPark',
                in: 'path',
                required: true,
                description: 'Car Park UUID',
                schema: new OA\Schema(type: 'string', example: 'c4d11d0a-586c-4150-a5ed-0bd610af9ff9')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/CreateBookingRequest')
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Booking created',
                content: new OA\JsonContent(ref: '#/components/schemas/CreateBookingResponse')
            ),
            new OA\Response(
                response: 400,
                description: 'Bad request',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorBadRequest')
            ),
            new OA\Response(
                response: 404,
                description: 'Car park not found',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorNotFound')
            ),
            new OA\Response(
                response: 409,
                description: 'Overlap conflict',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorConflict')
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorValidation')
            ),
        ]
    )]
    public function store(CarParkModel $carPark, CreateBookingDTO $data): JsonResponse
    {
        $result = $this->service->handle($carPark, $data);

        return response()->json($result->toArray(), 201);
    }

    #[OA\Patch(
        path: '/bookings/{booking}',
        operationId: 'amendBooking',
        summary: 'Amend an existing booking',
        description: 'Update an existing booking (dates, period). Requires proof of ownership (reference + email + vehicle registration)',
        tags: ['Bookings'],
        parameters: [
            new OA\Parameter(
                name: 'booking',
                in: 'path',
                required: true,
                description: 'Booking UUID',
                schema: new OA\Schema(type: 'string', example: 'c5312b5f-6c55-4a15-9d2c-0a2d2d7c9a2f')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/AmendBookingRequest')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Booking amended',
                content: new OA\JsonContent(ref: '#/components/schemas/BookingResponse')
            ),
            new OA\Response(
                response: 400,
                description: 'Bad request',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorBadRequest')
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden (ownership proof failed)',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorForbidden')
            ),
            new OA\Response(
                response: 404,
                description: 'Booking not found',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorNotFound')
            ),
            new OA\Response(
                response: 409,
                description: 'Date overlap conflict',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorConflict')
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorValidation')
            ),
        ]
    )]
    public function update(BookingModel $booking, AmendBookingDTO $data): JsonResponse
    {
        $result = $this->amendBookingService->handle($booking, $data);

        return response()->json($result->toArray(), 200);
    }

    #[OA\Post(
        path: '/bookings/{booking}/cancel',
        operationId: 'cancelBooking',
        summary: 'Cancel an existing booking',
        description: 'Cancels a booking and releases its reserved days. Requires proof of ownership (reference + email + vehicle registration).',
        tags: ['Bookings'],
        parameters: [
            new OA\Parameter(
                name: 'booking',
                in: 'path',
                required: true,
                description: 'Booking identifier (route model binding)',
                schema: new OA\Schema(type: 'string', example: 'c5312b5f-6c55-4a15-9d2c-0a2d2d7c9a2f')
            ),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/CancelBookingRequest')
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Booking cancelled',
                content: new OA\JsonContent(ref: '#/components/schemas/CancelBookingResponse')
            ),
            new OA\Response(
                response: 400,
                description: 'Bad request',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorBadRequest')
            ),
            new OA\Response(
                response: 403,
                description: 'Forbidden (ownership proof failed)',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorForbidden')
            ),
            new OA\Response(
                response: 404,
                description: 'Booking not found',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorNotFound')
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorValidation')
            ),
        ]
    )]
    public function cancel(BookingModel $booking, CancelBookingDTO $data): JsonResponse
    {
        $result = $this->cancelBookingService->handle($booking, $data);

        return response()->json($result->toArray(), 200);
    }
}
