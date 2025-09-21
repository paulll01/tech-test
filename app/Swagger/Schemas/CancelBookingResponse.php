<?php

namespace App\Swagger\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CancelBookingResponse',
    type: 'object',
    required: ['booking_uuid', 'reference', 'status', 'cancelled_at'],
    properties: [
        new OA\Property(property: 'booking_uuid', type: 'string', example: 'c5312b5f-6c55-4a15-9d2c-0a2d2d7c9a2f'),
        new OA\Property(property: 'reference', type: 'string', example: 'NVN-ABC123'),
        new OA\Property(property: 'status', type: 'string', example: 'cancelled'),
        new OA\Property(property: 'cancelled_at', type: 'string', format: 'date-time', example: '2025-09-21T14:32:10Z'),
    ],
)]
class CancelBookingResponse {}
