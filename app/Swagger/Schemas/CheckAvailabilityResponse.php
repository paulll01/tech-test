<?php

namespace App\Swagger\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'CheckAvailabilityResponse',
    type: 'object',
    required: ['car_park', 'capacity', 'days', 'availability', 'total_price', 'currency'],
    properties: [
        new OA\Property(property: 'car_park', type: 'string', example: 'T1'),
        new OA\Property(property: 'capacity', type: 'integer', example: 10),
        new OA\Property(
            property: 'days',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/AvailabilityDay')
        ),
        new OA\Property(property: 'availability', type: 'boolean', example: true),
        new OA\Property(property: 'total_price', type: 'string', example: '295.00'),
        new OA\Property(property: 'currency', type: 'string', example: 'GBP'),
    ],
    example: [
        'car_park' => 'T1',
        'capacity' => 10,
        'days' => [
            [
                'date' => '2025-09-21',
                'available' => 10,
                'price' => 50,
                'is_weekend' => true,
                'price_source' => 'season:Summer 2025',
            ],
            [
                'date' => '2025-09-22',
                'available' => 9,
                'price' => 35,
                'is_weekend' => false,
                'price_source' => 'season:Summer 2025',
            ],
        ],
        'availability' => true,
        'total_price' => '295.00',
        'currency' => 'GBP',
    ]
)]
class CheckAvailabilityResponse {}
