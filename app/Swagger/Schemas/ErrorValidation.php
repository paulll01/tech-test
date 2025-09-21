<?php

namespace App\Swagger\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ErrorValidation',
    type: 'object',
    required: ['message', 'errors'],
    properties: [
        new OA\Property(
            property: 'message',
            type: 'string',
            example: 'The given data was invalid.'
        ),
        new OA\Property(
            property: 'errors',
            type: 'object',
            additionalProperties: new OA\AdditionalProperties(
                type: 'array',
                items: new OA\Items(type: 'string')
            ),
            example: [
                'from' => [
                    'The from field must be a valid date.',
                    'The from field must be a date after or equal to today.',
                ],
                'to' => [
                    'The to field must be a date after or equal to from.',
                ],
            ]
        ),
    ],
)]
class ErrorValidation {}
