<?php

namespace Tests\Unit\Services;

use App\Exceptions\Booking\OwnershipMismatchException;
use App\Helpers\Booking\BookingOwnershipProof;
use App\Models\BookingModel;
use App\Services\Booking\BookingOwnershipService;
use Mockery;
use Tests\TestCase;

class BookingOwnershipServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @return BookingModel&\Mockery\MockInterface */
    private function mockBooking(array $attrs)
    {
        $booking = Mockery::mock(BookingModel::class);
        $booking->shouldReceive('getAttribute')
            ->andReturnUsing(fn ($key) => $attrs[$key] ?? null);

        return $booking;
    }

    /** @return BookingOwnershipProof&\Mockery\MockInterface */
    private function mockProof(string $ref, string $email, string $reg)
    {
        $proof = Mockery::mock(BookingOwnershipProof::class);
        $proof->shouldReceive('getUniqueReference')->andReturn($ref);
        $proof->shouldReceive('getCustomerEmail')->andReturn($email);
        $proof->shouldReceive('getVehicleReg')->andReturn($reg);

        return $proof;
    }

    public function test_verify_throws_when_reference_mismatch(): void
    {
        $booking = $this->mockBooking([
            'unique_reference' => 'NVN-ABC123',
            'customer_email' => 'test@example.com',
            'vehicle_reg' => 'KL56NVN',
        ]);

        // mismatch
        $proof = $this->mockProof(
            ref: 'NVN-XYZ999',
            email: 'test@example.com',
            reg: 'KL56NVN'
        );

        $service = new BookingOwnershipService;

        $this->expectException(OwnershipMismatchException::class);

        $service->verify($booking, $proof);
    }

    public function test_verify_throws_when_email_mismatch(): void
    {
        $booking = $this->mockBooking([
            'unique_reference' => 'NVN-ABC123',
            'customer_email' => 'test@example.com',
            'vehicle_reg' => 'KL56NVN',
        ]);

        // mismatch
        $proof = $this->mockProof(
            ref: 'NVN-ABC123',
            email: 'wrong@example.com',
            reg: 'KL56NVN'
        );

        $service = new BookingOwnershipService;

        $this->expectException(OwnershipMismatchException::class);

        $service->verify($booking, $proof);
    }

    public function test_verify_throws_when_vehicle_reg_mismatch(): void
    {
        $booking = $this->mockBooking([
            'unique_reference' => 'NVN-ABC123',
            'customer_email' => 'test@example.com',
            'vehicle_reg' => 'KL56NVN',
        ]);

        // mismatch
        $proof = $this->mockProof(
            ref: 'NVN-ABC123',
            email: 'test@example.com',
            reg: 'AB12CDE'
        );

        $service = new BookingOwnershipService;

        $this->expectException(OwnershipMismatchException::class);

        $service->verify($booking, $proof);
    }
}
