<?php

namespace App\Swagger\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CreateBookingRequest',
    type: 'object',
    required: ['from', 'to', 'customer_email', 'vehicle_reg'],
    properties: [
        new OA\Property(property: 'from', type: 'string', format: 'date', example: '2025-09-21', description: "Y-m-d, ≥ today, ≤ 'to'"),
        new OA\Property(property: 'to', type: 'string', format: 'date', example: '2025-09-25', description: "Y-m-d, ≥ 'from'"),
        new OA\Property(property: 'customer_email', type: 'string', format: 'email', example: 'test@example.com'),
        new OA\Property(property: 'vehicle_reg', type: 'string', maxLength: 16, example: 'KL56NVN'),
    ],
)]
class CreateBookingRequest {}
