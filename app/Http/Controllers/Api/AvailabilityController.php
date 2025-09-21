<?php

namespace App\Http\Controllers\Api;

use App\DTO\Availability\CheckAvailabilityDTO;
use App\Http\Controllers\Controller;
use App\Models\CarParkModel;
use App\Services\ApplicationServices\Availability\ICheckAvailabilityService;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class AvailabilityController extends Controller
{
    public function __construct(
        private ICheckAvailabilityService $checkAvailabilityService,

    ) {}

    #[OA\Get(
        path: '/{carPark}/availability',
        operationId: 'checkAvailability',
        summary: 'Check car park availability for a date range',
        description: 'Returns availability information for a given car park and date range.',
        tags: ['Availability'],
        parameters: [
            new OA\Parameter(
                name: 'carPark',
                in: 'path',
                required: true,
                description: 'Car Park uuid',
                schema: new OA\Schema(type: 'string', example: 'c4d11d0a-586c-4150-a5ed-0bd610af9ff9')
            ),
            new OA\Parameter(
                name: 'from',
                in: 'query',
                required: true,
                description: "Start date (inclusive). Format: Y-m-d. Must be today or later, and ≤ 'to'.",
                schema: new OA\Schema(type: 'string', format: 'date', example: '2025-09-21')
            ),
            new OA\Parameter(
                name: 'to',
                in: 'query',
                required: true,
                description: "End date (inclusive). Format: Y-m-d. Must be ≥ 'from'.",
                schema: new OA\Schema(type: 'string', format: 'date', example: '2025-09-25')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Availability result',
                content: new OA\JsonContent(ref: '#/components/schemas/CheckAvailabilityResponse')
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
                response: 422,
                description: 'Validation error',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorValidation')
            ),
        ]
    )]
    public function check(CarParkModel $carPark, CheckAvailabilityDTO $data): JsonResponse
    {
        $result = $this->checkAvailabilityService->handle($carPark, $data);

        return response()->json($result->toArray());
    }
}
