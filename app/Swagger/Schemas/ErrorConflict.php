<?php

namespace App\Swagger\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ErrorConflict',
    type: 'object',
    required: ['message'],
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Vehicle has overlapping bookings for the given dates.'),
        new OA\Property(
            property: 'overlapping_dates',
            type: 'array',
            items: new OA\Items(type: 'string', format: 'date'),
            nullable: true,
            example: ['2025-09-22']
        ),
    ],
)]
class ErrorConflict {}
