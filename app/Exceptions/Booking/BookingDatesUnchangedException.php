<?php

namespace App\Exceptions\Booking;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class BookingDatesUnchangedException extends Exception
{
    public function __construct(
        public readonly string $from,
        public readonly string $to,
        string $message = 'Booking dates have not changed.'
    ) {
        parent::__construct($message);
    }

    public function render($request): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'errors' => [
                'from' => ['No change detected.'],
                'to' => ['No change detected.'],
            ],
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
