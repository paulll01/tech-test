<?php

namespace App\Exceptions\Booking;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class CapacityExceededException extends Exception
{
    /** @param string[] $days */
    public function __construct(public array $days, string $message = 'One or more days are fully booked.')
    {
        parent::__construct($message);
    }

    public function render($request): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'errors' => [
                'blocking_days' => $this->days,
            ],
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
