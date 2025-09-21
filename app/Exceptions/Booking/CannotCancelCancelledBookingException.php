<?php

namespace App\Exceptions\Booking;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class CannotCancelCancelledBookingException extends Exception
{
    public function __construct(string $message = 'Booking already cancelled.')
    {
        parent::__construct($message);
    }

    public function render($request): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'errors' => ['status' => ['Booking is already cancelled.']],
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
