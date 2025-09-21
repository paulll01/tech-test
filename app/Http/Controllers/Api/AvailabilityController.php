<?php

namespace App\Http\Controllers\Api;

use App\DTO\Availability\CheckAvailabilityDTO;
use App\Http\Controllers\Controller;
use App\Models\CarParkModel;
use App\Services\ApplicationServices\Availability\ICheckAvailabilityService;
use Illuminate\Http\JsonResponse;

class AvailabilityController extends Controller
{
    public function __construct(
        private ICheckAvailabilityService $checkAvailabilityService,

    ) {}

    public function check(CarParkModel $carPark, CheckAvailabilityDTO $data): JsonResponse
    {
        $result = $this->checkAvailabilityService->handle($carPark, $data);

        return response()->json($result->toArray());
    }
}
