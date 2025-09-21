<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;

#[OA\Info(version: '1.0.0', title: 'Parking API')]
#[OA\Server(url: '/api', description: 'Cavu Tech Test')]
class OpenApi {}
