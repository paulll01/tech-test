<?php

namespace Tests\Unit\Services\ApplicationServices;

use App\DTO\Booking\CancelBookingDTO;
use App\DTO\Booking\CancelBookingResponseDTO;
use App\Exceptions\Booking\CannotCancelCancelledBookingException;
use App\Exceptions\Booking\OwnershipMismatchException;
use App\Models\BookingModel;
use App\Repositories\Booking\IBookingRepository;
use App\Services\ApplicationServices\Booking\CancelBookingService;
use App\Services\Booking\IBookingOwnershipService;
use Illuminate\Support\Carbon;
use Mockery;
use Tests\TestCase;

class CancelBookingServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @return BookingModel&\Mockery\MockInterface */
    private function mockBooking(array $attrs)
    {
        /** @var BookingModel&\Mockery\MockInterface $booking */
        $booking = Mockery::mock(BookingModel::class);
        $booking->shouldReceive('getAttribute')
            ->andReturnUsing(fn ($key) => $attrs[$key] ?? null);

        return $booking;
    }

    public function test_handle_cancels_booking_and_returns_dto(): void
    {
        Carbon::setTestNow(Carbon::parse('2025-09-21 12:00:00', 'UTC'));

        /** @var IBookingRepository&\Mockery\MockInterface $bookingRepo */
        $bookingRepo = Mockery::mock(IBookingRepository::class);
        /** @var IBookingOwnershipService&\Mockery\MockInterface $ownership */
        $ownership = Mockery::mock(IBookingOwnershipService::class);

        $service = new CancelBookingService($bookingRepo, $ownership);

        $booking = $this->mockBooking([
            'status' => 'pending',
            'from_date' => '2025-09-21',
            'to_date' => '2025-09-25',
            'uuid' => 'uuid-123',
            'unique_reference' => 'NVN-ABC123',
        ]);

        $dto = new CancelBookingDTO(
            unique_reference: 'NVN-ABC123',
            customer_email: 'test@example.com',
            vehicle_reg: 'KL56NVN',
        );

        // ownership check
        $ownership->shouldReceive('verify')
            ->once()
            ->withArgs([$booking, $dto])
            ->andReturnNull();

        $updated = $this->mockBooking([
            'uuid' => 'uuid-123',
            'unique_reference' => 'NVN-ABC123',
            'status' => 'cancelled',
        ]);

        $bookingRepo->shouldReceive('cancelAndReleaseDays')
            ->once()
            ->with($booking)
            ->andReturn($updated);

        $resp = $service->handle($booking, $dto);

        $this->assertInstanceOf(CancelBookingResponseDTO::class, $resp);
        $this->assertSame('uuid-123', $resp->booking_uuid);
        $this->assertSame('NVN-ABC123', $resp->reference);
        $this->assertSame('cancelled', $resp->status);
        $this->assertSame(Carbon::now()->toIso8601String(), $resp->cancelled_at);

        Carbon::setTestNow();
    }

    public function test_handle_throws_cannot_cancel_cancelled(): void
    {
        /** @var IBookingRepository&\Mockery\MockInterface $bookingRepo */
        $bookingRepo = Mockery::mock(IBookingRepository::class);
        /** @var IBookingOwnershipService&\Mockery\MockInterface $ownership */
        $ownership = Mockery::mock(IBookingOwnershipService::class);

        $service = new CancelBookingService($bookingRepo, $ownership);

        $booking = $this->mockBooking([
            'status' => 'cancelled',
        ]);

        $dto = new CancelBookingDTO(
            unique_reference: 'NVN-ABC123',
            customer_email: 'test@example.com',
            vehicle_reg: 'KL56NVN',
        );

        $ownership->shouldNotReceive('verify');
        $bookingRepo->shouldNotReceive('cancelAndReleaseDays');

        $this->expectException(CannotCancelCancelledBookingException::class);

        $service->handle($booking, $dto);
    }

    public function test_handle_propagates_ownership_mismatch_and_does_not_cancel(): void
    {
        /** @var IBookingRepository&\Mockery\MockInterface $bookingRepo */
        $bookingRepo = Mockery::mock(IBookingRepository::class);
        /** @var IBookingOwnershipService&\Mockery\MockInterface $ownership */
        $ownership = Mockery::mock(IBookingOwnershipService::class);

        $service = new CancelBookingService($bookingRepo, $ownership);

        $booking = $this->mockBooking([
            'status' => 'pending',
            'from_date' => '2025-09-21',
            'to_date' => '2025-09-25',
            'uuid' => 'uuid-123',
            'unique_reference' => 'NVN-ABC123',
            'customer_email' => 'test@example.com',
            'vehicle_reg' => 'KL56NVN',
        ]);

        $dto = new CancelBookingDTO(
            unique_reference: 'NVN-ABC123',
            customer_email: 'wrong@example.com',
            vehicle_reg: 'KL56NVN',
        );

        $ownership->shouldReceive('verify')
            ->once()
            ->withArgs([$booking, $dto])
            ->andThrow(new OwnershipMismatchException(false, false, true));

        $bookingRepo->shouldNotReceive('cancelAndReleaseDays');

        $this->expectException(OwnershipMismatchException::class);

        $service->handle($booking, $dto);
    }
}
