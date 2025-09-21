<?php

namespace App\Swagger\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AvailabilityDay',
    type: 'object',
    required: ['date', 'available', 'price', 'is_weekend', 'price_source'],
    properties: [
        new OA\Property(property: 'date', type: 'string', format: 'date', example: '2025-09-21'),
        new OA\Property(property: 'available', type: 'integer', example: 10),
        new OA\Property(property: 'price', type: 'number', format: 'float', example: 50),
        new OA\Property(property: 'is_weekend', type: 'boolean', example: true),
        new OA\Property(property: 'price_source', type: 'string', example: 'season:Summer 2025'),
    ],
)]
class AvailabilityDay {}
