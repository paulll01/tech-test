<?php

namespace App\Exceptions\Booking;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class VehicleOverlapException extends Exception
{
    /** @param string[] $days */
    public function __construct(public array $days, string $message = 'Vehicle already has a booking overlapping these dates.')
    {
        parent::__construct($message);
    }

    public function render($request): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'errors' => [
                'vehicle_reg' => ['Vehicle already has a booking overlapping these dates.'],
                'overlap_days' => $this->days,
            ],
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
