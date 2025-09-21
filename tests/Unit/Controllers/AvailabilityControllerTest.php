<?php

namespace Tests\Unit\Controllers;

use App\DTO\Availability\CheckAvailabilityDTO;
use App\DTO\Availability\CheckAvailabilityResponseDTO;
use App\Http\Controllers\Api\AvailabilityController;
use App\Models\CarParkModel;
use App\Services\ApplicationServices\Availability\ICheckAvailabilityService;
use Illuminate\Http\JsonResponse;
use Mockery;
use Tests\TestCase;

class AvailabilityControllerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_check_returns_json_from_service(): void
    {
        /** @var ICheckAvailabilityService&\Mockery\MockInterface $serviceMock */
        $serviceMock = Mockery::mock(ICheckAvailabilityService::class);

        /** @var CarParkModel $carPark */
        $carPark = CarParkModel::factory()->make([
            'id' => 1,
            'name' => 'T1',
            'capacity' => 10,
            'default_weekday_price' => 30.00,
            'default_weekend_price' => 40.00,
        ]);

        $dto = new CheckAvailabilityDTO(
            from: '2025-10-01',
            to: '2025-10-05',
        );

        $expectedDto = new CheckAvailabilityResponseDTO(
            carParkName: 'T1',
            capacity: 10,
            days: [
                ['date' => '2025-10-01', 'available' => 10],
                ['date' => '2025-10-02', 'available' => 9],
                ['date' => '2025-10-03', 'available' => 10],
                ['date' => '2025-10-04', 'available' => 8],
                ['date' => '2025-10-05', 'available' => 10],
            ],
            available: true,
            totalPrice: 0.0,
            currency: 'GBP',
        );

        $serviceMock->shouldReceive('handle')
            ->once()
            ->withArgs([$carPark, $dto])
            ->andReturn($expectedDto);

        $controller = new AvailabilityController($serviceMock);

        $response = $controller->check($carPark, $dto);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame($expectedDto->toArray(), $response->getData(true));
    }
}
