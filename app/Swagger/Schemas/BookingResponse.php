<?php

namespace App\Swagger\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'BookingResponse',
    type: 'object',
    required: ['booking_uuid', 'reference', 'car_park', 'from', 'to', 'days', 'total_price', 'currency', 'status'],
    properties: [
        new OA\Property(property: 'booking_uuid', type: 'string', example: 'c5312b5f-6c55-4a15-9d2c-0a2d2d7c9a2f'),
        new OA\Property(property: 'reference', type: 'string', example: 'NVN-ABC123'),
        new OA\Property(property: 'car_park', type: 'string', example: 'T1'),
        new OA\Property(property: 'from', type: 'string', format: 'date', example: '2025-10-01'),
        new OA\Property(property: 'to', type: 'string', format: 'date', example: '2025-10-05'),
        new OA\Property(
            property: 'days',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/BookingDayPrice')
        ),
        new OA\Property(property: 'total_price', type: 'string', example: '150.00'),
        new OA\Property(property: 'currency', type: 'string', example: 'GBP'),
        new OA\Property(property: 'status', type: 'string', example: 'pending'),
    ],
)]
class BookingResponse {}
