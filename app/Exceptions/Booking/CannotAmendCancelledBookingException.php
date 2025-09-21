<?php

namespace App\Exceptions\Booking;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class CannotAmendCancelledBookingException extends Exception
{
    public function __construct(string $message = 'Cannot amend a cancelled booking.')
    {
        parent::__construct($message);
    }

    public function render($request): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'errors' => [
                'status' => ['Booking is cancelled and cannot be amended.'],
            ],
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
