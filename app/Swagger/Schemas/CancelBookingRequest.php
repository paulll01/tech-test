<?php

namespace App\Swagger\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CancelBookingRequest',
    type: 'object',
    required: ['unique_reference', 'customer_email', 'vehicle_reg'],
    properties: [
        new OA\Property(property: 'unique_reference', type: 'string', example: 'NVN-ABC123'),
        new OA\Property(property: 'customer_email', type: 'string', format: 'email', example: 'test@example.com'),
        new OA\Property(property: 'vehicle_reg', type: 'string', maxLength: 16, example: 'KL56NVN'),
    ],
    example: [
        'unique_reference' => 'NVN-ABC123',
        'customer_email' => 'test@example.com',
        'vehicle_reg' => 'KL56NVN',
    ]
)]
class CancelBookingRequest {}
