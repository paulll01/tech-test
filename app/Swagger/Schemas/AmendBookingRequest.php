<?php

namespace App\Swagger\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'AmendBookingRequest',
    type: 'object',
    required: ['vehicle_reg', 'customer_email', 'unique_reference'],
    properties: [
        new OA\Property(property: 'from', type: 'string', format: 'date', nullable: true, example: '2025-10-01'),
        new OA\Property(property: 'to', type: 'string', format: 'date', nullable: true, example: '2025-10-05'),

        new OA\Property(property: 'vehicle_reg', type: 'string', maxLength: 7, example: 'KL56NVN'),
        new OA\Property(property: 'customer_email', type: 'string', format: 'email', example: 'test@example.com'),
        new OA\Property(property: 'unique_reference', type: 'string', example: 'NVN-ABC123'),
    ],
    example: [
        'from' => '2025-10-01',
        'to' => '2025-10-05',
        'vehicle_reg' => 'KL56NVN',
        'customer_email' => 'test@example.com',
        'unique_reference' => 'NVN-ABC123',
    ]
)]
class AmendBookingRequest {}
