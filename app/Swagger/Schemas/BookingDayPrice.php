<?php

namespace App\Swagger\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'BookingDayPrice',
    type: 'object',
    required: ['date', 'price'],
    properties: [
        new OA\Property(property: 'date', type: 'string', format: 'date', example: '2025-09-21'),
        new OA\Property(property: 'price', type: 'number', format: 'float', example: 35.0),
    ],
)]
class BookingDayPrice {}
