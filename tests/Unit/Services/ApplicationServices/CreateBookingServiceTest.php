<?php

namespace Tests\Unit\Services\ApplicationServices;

use App\DTO\Booking\CreateBookingDTO;
use App\DTO\Booking\CreateBookingResponseDTO;
use App\DTO\Pricing\PricingQuoteDTO;
use App\Events\Booking\BookingCreatedEvent;
use App\Exceptions\Booking\CapacityExceededException;
use App\Exceptions\Booking\VehicleOverlapException;
use App\Models\BookingModel;
use App\Models\CarParkModel;
use App\Repositories\Booking\IBookingRepository;
use App\Repositories\BookingDay\IBookingDayRepository;
use App\Services\ApplicationServices\Booking\CreateBookingService;
use App\Services\BookingDay\IBookingDayService;
use App\Services\Pricing\IQuotePricingService;
use Illuminate\Support\Facades\Event;
use Mockery;
use Tests\TestCase;

class CreateBookingServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_handle_creates_booking_and_dispatches_event_and_returns_dto(): void
    {
        /** @var IBookingDayService&\Mockery\MockInterface $bookingDayService */
        $bookingDayService = Mockery::mock(IBookingDayService::class);

        /** @var IBookingDayRepository&\Mockery\MockInterface $bookingDayRepo */
        $bookingDayRepo = Mockery::mock(IBookingDayRepository::class);

        /** @var IQuotePricingService&\Mockery\MockInterface $pricingService */
        $pricingService = Mockery::mock(IQuotePricingService::class);

        /** @var IBookingRepository&\Mockery\MockInterface $bookingRepo */
        $bookingRepo = Mockery::mock(IBookingRepository::class);

        Event::fake([BookingCreatedEvent::class]);

        $service = new CreateBookingService(
            bookingDayService: $bookingDayService,
            bookingDayRepo: $bookingDayRepo,
            pricingService: $pricingService,
            bookingRepo: $bookingRepo,
        );

        /** @var CarParkModel $carPark */
        $carPark = CarParkModel::factory()->make([
            'id' => 10,
            'name' => 'T1',
            'capacity' => 5,
        ]);

        $dto = new CreateBookingDTO(
            from: '2025-09-21',
            to: '2025-09-23',
            customer_email: 'john@example.com',
            vehicle_reg: 'KL56NVN',
        );

        $days = ['2025-09-21', '2025-09-22', '2025-09-23'];

        $bookingDayService->shouldReceive('getBookedPerDay')
            ->once()
            ->andReturn([
                '2025-09-21' => 0,
                '2025-09-22' => 1,
                '2025-09-23' => 0,
            ]);

        $bookingDayRepo->shouldReceive('vehicleOverlaps')
            ->once()
            ->withArgs(function (int $carParkId, string $vehicleReg, array $checkedDays) use ($carPark, $days) {
                return $carParkId === $carPark->id
                    && $vehicleReg === 'KL56NVN'
                    && $checkedDays === $days;
            })
            ->andReturn([]);

        $quote = new PricingQuoteDTO(
            [
                '2025-09-21' => ['price' => 30.0],
                '2025-09-22' => ['price' => 30.0],
                '2025-09-23' => ['price' => 40.0],
            ],
            100.0,
            'GBP',
        );

        $pricingService->shouldReceive('quote')
            ->once()
            ->andReturn($quote);

        /** @var BookingModel $bookingModel */
        $bookingModel = BookingModel::factory()->make([
            'uuid' => 'uuid-123',
            'car_park_id' => $carPark->id,
            'vehicle_reg' => 'KL56NVN',
            'status' => 'pending',
        ]);

        $bookingRepo->shouldReceive('createWithDays')
            ->once()
            ->withArgs(function (array $attrs, array $persistDays) use ($carPark, $days) {
                return $attrs['car_park_id'] === $carPark->id
                    && $attrs['vehicle_reg'] === 'KL56NVN'
                    && $attrs['status'] === 'pending'
                    && $attrs['from_date'] === '2025-09-21'
                    && $attrs['to_date'] === '2025-09-23'
                    && $persistDays === $days;
            })
            ->andReturn($bookingModel);

        $responseDto = $service->handle($carPark, $dto);

        $this->assertInstanceOf(CreateBookingResponseDTO::class, $responseDto);
        $this->assertSame('uuid-123', $responseDto->booking_uuid);
        $this->assertSame('T1', $responseDto->car_park);
        $this->assertSame('2025-09-21', $responseDto->from);
        $this->assertSame('2025-09-23', $responseDto->to);
        $this->assertSame(100.0, $responseDto->total_price);
        $this->assertSame('GBP', $responseDto->currency);
        $this->assertSame('pending', $responseDto->status);

        $this->assertSame(
            [
                ['date' => '2025-09-21', 'price' => 30.0],
                ['date' => '2025-09-22', 'price' => 30.0],
                ['date' => '2025-09-23', 'price' => 40.0],
            ],
            $responseDto->days
        );

        Event::assertDispatched(BookingCreatedEvent::class);
    }

    public function test_handle_throws_capacity_exceeded_and_does_not_create_or_dispatch(): void
    {
        /** @var \App\Services\BookingDay\IBookingDayService&\Mockery\MockInterface $bookingDayService */
        $bookingDayService = Mockery::mock(IBookingDayService::class);

        /** @var \App\Repositories\BookingDay\IBookingDayRepository&\Mockery\MockInterface $bookingDayRepo */
        $bookingDayRepo = Mockery::mock(IBookingDayRepository::class);

        /** @var \App\Services\Pricing\IQuotePricingService&\Mockery\MockInterface $pricingService */
        $pricingService = Mockery::mock(IQuotePricingService::class);

        /** @var \App\Repositories\Booking\IBookingRepository&\Mockery\MockInterface $bookingRepo */
        $bookingRepo = Mockery::mock(IBookingRepository::class);

        $service = new CreateBookingService(
            $bookingDayService,
            $bookingDayRepo,
            $pricingService,
            $bookingRepo
        );

        /** @var \App\Models\CarParkModel $carPark */
        $carPark = CarParkModel::factory()->make(['id' => 10, 'name' => 'T1', 'capacity' => 1]);

        $dto = new CreateBookingDTO(
            from: '2025-09-21',
            to: '2025-09-23',
            customer_email: 'john@example.com',
            vehicle_reg: 'KL56NVN',
        );

        // fully booked
        $bookedMap = [
            '2025-09-21' => 1,
            '2025-09-22' => 0,
            '2025-09-23' => 0,
        ];

        $bookingDayService->shouldReceive('getBookedPerDay')
            ->once()
            ->andReturn($bookedMap);

        $bookingDayRepo->shouldNotReceive('vehicleOverlaps');
        $pricingService->shouldNotReceive('quote');
        $bookingRepo->shouldNotReceive('createWithDays');

        Event::fake();

        $this->expectException(CapacityExceededException::class);

        $service->handle($carPark, $dto);

        // assert not dispatched
        Event::assertNotDispatched(BookingCreatedEvent::class);
    }

    public function test_handle_throws_vehicle_overlap_and_does_not_persist_or_dispatch(): void
    {
        /** @var \App\Services\BookingDay\IBookingDayService&\Mockery\MockInterface $bookingDayService */
        $bookingDayService = Mockery::mock(IBookingDayService::class);

        /** @var \App\Repositories\BookingDay\IBookingDayRepository&\Mockery\MockInterface $bookingDayRepo */
        $bookingDayRepo = Mockery::mock(IBookingDayRepository::class);

        /** @var \App\Services\Pricing\IQuotePricingService&\Mockery\MockInterface $pricingService */
        $pricingService = Mockery::mock(IQuotePricingService::class);

        /** @var \App\Repositories\Booking\IBookingRepository&\Mockery\MockInterface $bookingRepo */
        $bookingRepo = Mockery::mock(IBookingRepository::class);

        $service = new CreateBookingService(
            $bookingDayService,
            $bookingDayRepo,
            $pricingService,
            $bookingRepo
        );

        /** @var \App\Models\CarParkModel $carPark */
        $carPark = CarParkModel::factory()->make(['id' => 10, 'name' => 'T1', 'capacity' => 5]);

        $dto = new CreateBookingDTO(
            from: '2025-09-21',
            to: '2025-09-23',
            customer_email: 'john@example.com',
            vehicle_reg: 'KL56NVN',
        );

        $bookedMap = [
            '2025-09-21' => 0,
            '2025-09-22' => 0,
            '2025-09-23' => 0,
        ];

        $bookingDayService->shouldReceive('getBookedPerDay')
            ->once()
            ->andReturn($bookedMap);

        // overlaps
        $overlaps = ['2025-09-22'];

        $bookingDayRepo->shouldReceive('vehicleOverlaps')
            ->once()
            ->andReturn($overlaps);

        $pricingService->shouldNotReceive('quote');
        $bookingRepo->shouldNotReceive('createWithDays');

        Event::fake();

        $this->expectException(VehicleOverlapException::class);

        $service->handle($carPark, $dto);

        // assert not dispatched
        Event::assertNotDispatched(BookingCreatedEvent::class);
    }
}
