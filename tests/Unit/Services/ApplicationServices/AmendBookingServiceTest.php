<?php

namespace Tests\Unit\Services\ApplicationServices;

use App\DTO\Booking\AmendBookingDTO;
use App\DTO\Booking\CreateBookingResponseDTO;
use App\DTO\Pricing\PricingQuoteDTO;
use App\Exceptions\Booking\BookingDatesUnchangedException;
use App\Exceptions\Booking\CannotAmendCancelledBookingException;
use App\Exceptions\Booking\CapacityExceededException;
use App\Exceptions\Booking\VehicleOverlapException;
use App\Models\BookingModel;
use App\Models\CarParkModel;
use App\Repositories\Booking\IBookingRepository;
use App\Repositories\BookingDay\IBookingDayRepository;
use App\Services\ApplicationServices\Booking\AmendBookingService;
use App\Services\Booking\IBookingOwnershipService;
use App\Services\BookingDay\IBookingDayService;
use App\Services\Pricing\IQuotePricingService;
use Mockery;
use Tests\TestCase;

class AmendBookingServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function mockBooking(array $attrs): \App\Models\BookingModel
    {
        $booking = \Mockery::mock(\App\Models\BookingModel::class);

        $booking->shouldReceive('getAttribute')
            ->andReturnUsing(function ($key) use ($attrs) {
                return $attrs[$key] ?? null;
            });

        return $booking;
    }

    public function test_handle_updates_booking_and_returns_dto(): void
    {
        /** @var IBookingDayService&\Mockery\MockInterface $bookingDayService */
        $bookingDayService = Mockery::mock(IBookingDayService::class);
        /** @var IBookingDayRepository&\Mockery\MockInterface $bookingDayRepo */
        $bookingDayRepo = Mockery::mock(IBookingDayRepository::class);
        /** @var IQuotePricingService&\Mockery\MockInterface $pricingService */
        $pricingService = Mockery::mock(IQuotePricingService::class);
        /** @var IBookingRepository&\Mockery\MockInterface $bookingRepo */
        $bookingRepo = Mockery::mock(IBookingRepository::class);
        /** @var IBookingOwnershipService&\Mockery\MockInterface $ownership */
        $ownership = Mockery::mock(IBookingOwnershipService::class);

        $service = new AmendBookingService(
            bookingDayService: $bookingDayService,
            bookingDayRepo: $bookingDayRepo,
            pricingService: $pricingService,
            bookingRepo: $bookingRepo,
            ownership: $ownership,
        );

        // existing
        /** @var CarParkModel $carPark */
        $carPark = CarParkModel::factory()->make([
            'id' => 10,
            'name' => 'T1',
            'capacity' => 5,
        ]);

        $booking = $this->mockBooking([
            'status' => 'pending',
            'from_date' => '2025-09-21',
            'to_date' => '2025-09-23',
            'vehicle_reg' => 'KL56NVN',
            'customer_email' => 'john@example.com',
            'uuid' => 'uuid-123',
            'unique_reference' => 'NVN-ABC123',
            'car_park_id' => $carPark->id,
            'carPark' => $carPark,
        ]);

        $dto = new AmendBookingDTO(
            from: '2025-09-22',
            to: '2025-09-24',
            vehicle_reg: 'KL56NVN',
            customer_email: 'john@example.com',
            unique_reference: 'NVN-ABC123',
        );

        // ownership
        $ownership->shouldReceive('verify')
            ->once()
            ->withArgs([$booking, $dto])
            ->andReturnNull();

        // capacity
        $bookingDayService->shouldReceive('getBookedPerDay')
            ->once()
            ->andReturn([
                '2025-09-22' => 5,
                '2025-09-23' => 4,
                '2025-09-24' => 4,
            ]);

        $bookingDayRepo->shouldReceive('vehicleOverlaps')
            ->once()
            ->withArgs(function (int $carParkId, string $vehicleReg, array $days) {
                return $carParkId === 10
                    && $vehicleReg === 'KL56NVN'
                    && $days === ['2025-09-22', '2025-09-23', '2025-09-24'];
            })
            ->andReturn(['2025-09-23']);

        $quote = new PricingQuoteDTO(
            days: [
                '2025-09-22' => ['price' => 30.0],
                '2025-09-23' => ['price' => 30.0],
                '2025-09-24' => ['price' => 40.0],
            ],
            total: 100.0,
            currency: 'GBP',
        );

        $pricingService->shouldReceive('quote')
            ->once()
            ->andReturn($quote);

        $updatedBooking = Mockery::mock(BookingModel::class);
        $updatedBooking->shouldReceive('getAttribute')
            ->andReturnUsing(function ($key) {
                return [
                    'uuid' => 'uuid-123',
                    'unique_reference' => 'NVN-ABC123',
                    'status' => 'pending',
                ][$key] ?? null;
            });

        $bookingRepo->shouldReceive('updateWithDays')
            ->once()
            ->withArgs(function ($b, array $attrs, array $days) {
                return $b instanceof BookingModel
                    && $attrs['from_date'] === '2025-09-22'
                    && $attrs['to_date'] === '2025-09-24'
                    && $attrs['total_price'] === 100.0
                    && $days === ['2025-09-22', '2025-09-23', '2025-09-24'];
            })
            ->andReturn($updatedBooking);

        $resp = $service->handle($booking, $dto);

        $this->assertInstanceOf(CreateBookingResponseDTO::class, $resp);
        $this->assertSame('uuid-123', $resp->booking_uuid);
        $this->assertSame('NVN-ABC123', $resp->reference);
        $this->assertSame('T1', $resp->car_park);
        $this->assertSame('2025-09-22', $resp->from);
        $this->assertSame('2025-09-24', $resp->to);
        $this->assertSame(100.0, $resp->total_price);
        $this->assertSame('GBP', $resp->currency);
        $this->assertSame('pending', $resp->status);
        $this->assertSame([
            ['date' => '2025-09-22', 'price' => 30.0],
            ['date' => '2025-09-23', 'price' => 30.0],
            ['date' => '2025-09-24', 'price' => 40.0],
        ], $resp->days);
    }

    public function test_handle_throws_cannot_amend_cancelled(): void
    {
        $bookingDayService = Mockery::mock(IBookingDayService::class);
        $bookingDayRepo = Mockery::mock(IBookingDayRepository::class);
        $pricingService = Mockery::mock(IQuotePricingService::class);
        $bookingRepo = Mockery::mock(IBookingRepository::class);
        $ownership = Mockery::mock(IBookingOwnershipService::class);

        $service = new AmendBookingService($bookingDayService, $bookingDayRepo, $pricingService, $bookingRepo, $ownership);

        $booking = $this->mockBooking([
            'status' => 'cancelled',
        ]);

        $dto = new AmendBookingDTO(
            from: '2025-09-22',
            to: '2025-09-24',
            vehicle_reg: 'KL56NVN',
            customer_email: 'john@example.com',
            unique_reference: 'NVN-ABC123',
        );

        $ownership->shouldNotReceive('verify');
        $bookingDayService->shouldNotReceive('getBookedPerDay');

        $this->expectException(CannotAmendCancelledBookingException::class);

        $service->handle($booking, $dto);
    }

    public function test_handle_throws_dates_unchanged(): void
    {
        $bookingDayService = Mockery::mock(IBookingDayService::class);
        $bookingDayRepo = Mockery::mock(IBookingDayRepository::class);
        $pricingService = Mockery::mock(IQuotePricingService::class);
        $bookingRepo = Mockery::mock(IBookingRepository::class);
        $ownership = Mockery::mock(IBookingOwnershipService::class);

        $service = new AmendBookingService($bookingDayService, $bookingDayRepo, $pricingService, $bookingRepo, $ownership);

        /** @var CarParkModel $carPark */
        $carPark = CarParkModel::factory()->make(['id' => 10, 'name' => 'T1', 'capacity' => 5]);

        $booking = $this->mockBooking([
            'status' => 'pending',
            'from_date' => '2025-09-21',
            'to_date' => '2025-09-23',
            'carPark' => $carPark,
        ]);

        $dto = new AmendBookingDTO(
            from: null,
            to: null,
            vehicle_reg: 'KL56NVN',
            customer_email: 'john@example.com',
            unique_reference: 'NVN-ABC123',
        );

        $ownership->shouldReceive('verify')->once()->withArgs([$booking, $dto])->andReturnNull();
        $bookingDayService->shouldNotReceive('getBookedPerDay');

        $this->expectException(BookingDatesUnchangedException::class);

        $service->handle($booking, $dto);
    }

    public function test_handle_throws_capacity_exceeded(): void
    {
        $bookingDayService = Mockery::mock(IBookingDayService::class);
        $bookingDayRepo = Mockery::mock(IBookingDayRepository::class);
        $pricingService = Mockery::mock(IQuotePricingService::class);
        $bookingRepo = Mockery::mock(IBookingRepository::class);
        $ownership = Mockery::mock(IBookingOwnershipService::class);

        $service = new AmendBookingService($bookingDayService, $bookingDayRepo, $pricingService, $bookingRepo, $ownership);

        /** @var CarParkModel $carPark */
        $carPark = CarParkModel::factory()->make(['id' => 10, 'name' => 'T1', 'capacity' => 2]);

        $booking = $this->mockBooking([
            'status' => 'pending',
            'from_date' => '2025-09-21',
            'to_date' => '2025-09-23',
            'vehicle_reg' => 'KL56NVN',
            'carPark' => $carPark,
        ]);

        $dto = new AmendBookingDTO(
            from: '2025-09-21',
            to: '2025-09-24',
            vehicle_reg: 'KL56NVN',
            customer_email: 'john@example.com',
            unique_reference: 'NVN-ABC123',
        );

        $ownership->shouldReceive('verify')->once()->withArgs([$booking, $dto])->andReturnNull();

        $bookingDayService->shouldReceive('getBookedPerDay')
            ->once()
            ->andReturn([
                '2025-09-21' => 2,
                '2025-09-22' => 2,
                '2025-09-23' => 2,
                '2025-09-24' => 2,
            ]);

        $bookingDayRepo->shouldNotReceive('vehicleOverlaps');
        $pricingService->shouldNotReceive('quote');
        $bookingRepo->shouldNotReceive('updateWithDays');

        $this->expectException(CapacityExceededException::class);

        $service->handle($booking, $dto);
    }

    public function test_handle_throws_vehicle_overlap(): void
    {
        $bookingDayService = Mockery::mock(IBookingDayService::class);
        $bookingDayRepo = Mockery::mock(IBookingDayRepository::class);
        $pricingService = Mockery::mock(IQuotePricingService::class);
        $bookingRepo = Mockery::mock(IBookingRepository::class);
        $ownership = Mockery::mock(IBookingOwnershipService::class);

        $service = new AmendBookingService($bookingDayService, $bookingDayRepo, $pricingService, $bookingRepo, $ownership);

        /** @var CarParkModel $carPark */
        $carPark = CarParkModel::factory()->make(['id' => 10, 'name' => 'T1', 'capacity' => 5]);

        $booking = $this->mockBooking([
            'status' => 'pending',
            'from_date' => '2025-09-21',
            'to_date' => '2025-09-23',
            'vehicle_reg' => 'KL56NVN',
            'carPark' => $carPark,
        ]);

        $dto = new AmendBookingDTO(
            from: '2025-09-22',
            to: '2025-09-24',
            vehicle_reg: 'KL56NVN',
            customer_email: 'john@example.com',
            unique_reference: 'NVN-ABC123',
        );

        $ownership->shouldReceive('verify')->once()->withArgs([$booking, $dto])->andReturnNull();

        // capacity ok
        $bookingDayService->shouldReceive('getBookedPerDay')
            ->once()
            ->andReturn([
                '2025-09-22' => 1,
                '2025-09-23' => 1,
                '2025-09-24' => 1,
            ]);

        // overlap on 2025-09-24
        $bookingDayRepo->shouldReceive('vehicleOverlaps')
            ->once()
            ->andReturn(['2025-09-24']);

        $pricingService->shouldNotReceive('quote');
        $bookingRepo->shouldNotReceive('updateWithDays');

        $this->expectException(VehicleOverlapException::class);

        $service->handle($booking, $dto);
    }
}
