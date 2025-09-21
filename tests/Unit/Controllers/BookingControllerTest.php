<?php

namespace Tests\Unit\Controllers;

use App\DTO\Booking\AmendBookingDTO;
use App\DTO\Booking\CancelBookingDTO;
use App\DTO\Booking\CancelBookingResponseDTO;
use App\DTO\Booking\CreateBookingDTO;
use App\DTO\Booking\CreateBookingResponseDTO;
use App\Exceptions\Booking\VehicleOverlapException;
use App\Http\Controllers\Api\BookingController;
use App\Models\BookingModel;
use App\Models\CarParkModel;
use App\Services\ApplicationServices\Booking\IAmendBookingService;
use App\Services\ApplicationServices\Booking\ICancelBookingService;
use App\Services\ApplicationServices\Booking\ICreateBookingService;
use Illuminate\Http\JsonResponse;
use Mockery;
use Tests\TestCase;

class BookingControllerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_store_returns_json_from_service(): void
    {
        /** @var ICreateBookingService&\Mockery\MockInterface $createMock */
        $createMock = Mockery::mock(ICreateBookingService::class);
        /** @var IAmendBookingService&\Mockery\MockInterface $amendMock */
        $amendMock = Mockery::mock(IAmendBookingService::class);
        /** @var ICancelBookingService&\Mockery\MockInterface $cancelMock */
        $cancelMock = Mockery::mock(ICancelBookingService::class);

        /** @var CarParkModel $carPark */
        $carPark = CarParkModel::factory()->make([
            'id' => 1,
            'name' => 'T1',
            'capacity' => 10,
            'default_weekday_price' => 30.00,
            'default_weekend_price' => 40.00,
        ]);

        $dto = new CreateBookingDTO(
            from: '2025-09-21',
            to: '2025-09-25',
            customer_email: 'test@example.com',
            vehicle_reg: 'KL56NVN',
        );

        $expectedDto = new CreateBookingResponseDTO(
            booking_uuid: 'uuid-123',
            reference: 'NVN-ABC123',
            car_park: 'T1',
            from: '2025-09-21',
            to: '2025-09-25',
            days: [
                ['date' => '2025-09-21', 'price' => number_format(30.00, 2)],
                ['date' => '2025-09-22', 'price' => number_format(30.00, 2)],
                ['date' => '2025-09-23', 'price' => number_format(30.00, 2)],
                ['date' => '2025-09-24', 'price' => number_format(40.00, 2)],
                ['date' => '2025-09-25', 'price' => number_format(40.00, 2)],
            ],
            total_price: 170.0,
            currency: 'GBP',
            status: 'confirmed',
        );

        $createMock->shouldReceive('handle')
            ->once()
            ->withArgs([$carPark, $dto])
            ->andReturn($expectedDto);

        $controller = new BookingController($createMock, $amendMock, $cancelMock);

        $response = $controller->store($carPark, $dto);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(201, $response->getStatusCode());
        $this->assertSame($expectedDto->toArray(), $response->getData(true));
    }

    public function test_store_throws_exception(): void
    {
        $createMock = Mockery::mock(ICreateBookingService::class);
        $amendMock = Mockery::mock(IAmendBookingService::class);
        $cancelMock = Mockery::mock(ICancelBookingService::class);

        /** @var \App\Models\CarParkModel $carPark */
        $carPark = CarParkModel::factory()->make(['id' => 1, 'name' => 'T1', 'capacity' => 10]);

        $dto = new CreateBookingDTO(
            from: '2025-09-21',
            to: '2025-09-25',
            customer_email: 'test@example.com',
            vehicle_reg: 'KL56NVN',
        );

        $createMock->shouldReceive('handle')
            ->once()
            ->withArgs([$carPark, $dto])
            ->andThrow(new VehicleOverlapException(['2025-09-22']));

        $this->expectException(VehicleOverlapException::class);

        $controller = new BookingController($createMock, $amendMock, $cancelMock);
        $controller->store($carPark, $dto);
    }

    public function test_update_returns_json_from_service(): void
    {
        $createMock = Mockery::mock(ICreateBookingService::class);
        $amendMock = Mockery::mock(IAmendBookingService::class);
        $cancelMock = Mockery::mock(ICancelBookingService::class);

        /** @var BookingModel $booking */
        $booking = BookingModel::factory()->make([
            'id' => 77,
            'uuid' => 'uuid-123',
            'car_park_id' => 1,
        ]);

        $dto = new AmendBookingDTO(
            from: '2025-09-22',
            to: '2025-09-26',
            vehicle_reg: 'KL56NVN',
            customer_email: 'test@example.com',
            unique_reference: 'NVN-ABC123',
        );

        $expectedDto = new CreateBookingResponseDTO(
            booking_uuid: 'uuid-123',
            reference: 'NVN-ABC123',
            car_park: 'T1',
            from: '2025-09-22',
            to: '2025-09-26',
            days: [
                ['date' => '2025-09-22', 'price' => number_format(30.00, 2)],
                ['date' => '2025-09-23', 'price' => number_format(30.00, 2)],
                ['date' => '2025-09-24', 'price' => number_format(40.00, 2)],
                ['date' => '2025-09-25', 'price' => number_format(40.00, 2)],
                ['date' => '2025-09-26', 'price' => number_format(40.00, 2)],
            ],
            total_price: 180.0,
            currency: 'GBP',
            status: 'pending',
        );

        $amendMock->shouldReceive('handle')
            ->once()
            ->withArgs([$booking, $dto])
            ->andReturn($expectedDto);

        $controller = new BookingController($createMock, $amendMock, $cancelMock);

        $response = $controller->update($booking, $dto);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame($expectedDto->toArray(), $response->getData(true));
    }

    public function test_cancel_returns_json_from_service(): void
    {
        $createMock = Mockery::mock(ICreateBookingService::class);
        $amendMock = Mockery::mock(IAmendBookingService::class);
        $cancelMock = Mockery::mock(ICancelBookingService::class);

        /** @var BookingModel $booking */
        $booking = BookingModel::factory()->make([
            'id' => 77,
            'uuid' => 'uuid-123',
            'car_park_id' => 1,
        ]);

        $dto = new CancelBookingDTO(
            unique_reference: 'NVN-ABC123',
            customer_email: 'test@example.com',
            vehicle_reg: 'KL56NVN',
        );

        $expectedDto = new CancelBookingResponseDTO(
            booking_uuid: 'uuid-123',
            reference: 'NVN-ABC123',
            status: 'cancelled',
            cancelled_at: '2025-09-21T12:00:00+00:00',
        );

        $cancelMock->shouldReceive('handle')
            ->once()
            ->withArgs([$booking, $dto])
            ->andReturn($expectedDto);

        $controller = new BookingController($createMock, $amendMock, $cancelMock);

        $response = $controller->cancel($booking, $dto);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame($expectedDto->toArray(), $response->getData(true));
    }
}
