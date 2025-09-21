<?php

namespace Tests\Unit\Repositories;

use App\Repositories\BookingDay\BookingDayRepository;
use Illuminate\Database\Eloquent\Builder;
use Mockery;
use Tests\TestCase;

class BookingDayRepositoryTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_count_booked_per_day_returns_counts_for_requested_days(): void
    {
        $carParkId = 10;
        $days = ['2025-09-21', '2025-09-22', '2025-09-23'];

        /** @var Builder&\Mockery\MockInterface $builder */
        $builder = Mockery::mock(Builder::class);

        $builder->shouldReceive('selectRaw')
            ->once()->with('date, COUNT(*) as booked')->andReturn($builder);

        $builder->shouldReceive('where')
            ->once()->with('car_park_id', $carParkId)->andReturn($builder);

        $builder->shouldReceive('whereIn')
            ->once()->with('date', $days)->andReturn($builder);

        $builder->shouldReceive('groupBy')
            ->once()->with('date')->andReturn($builder);

        $builder->shouldReceive('pluck')
            ->once()->with('booked', 'date')
            ->andReturn(collect([
                '2025-09-21' => 2,
                '2025-09-22' => 1,
                '2025-09-23' => 1,
            ]));

        Mockery::mock('alias:App\Models\BookingDayModel')
            ->shouldReceive('query')
            ->andReturn($builder);

        $repo = new BookingDayRepository;

        $result = $repo->countBookedPerDay($carParkId, $days);

        $this->assertSame([
            '2025-09-21' => 2,
            '2025-09-22' => 1,
            '2025-09-23' => 1,
        ], $result);
    }

    public function test_count_booked_per_day_returns_empty_when_days_empty(): void
    {
        $repo = new BookingDayRepository;

        $this->assertSame([], $repo->countBookedPerDay(10, []));
    }

    public function test_vehicle_overlaps_returns_unique_overlap_dates_for_same_vehicle_and_status(): void
    {
        $carParkId = 10;
        $vehicle = 'KL56NVN';
        $days = ['2025-09-21', '2025-09-22', '2025-09-23'];

        /** @var Builder&\Mockery\MockInterface $builder */
        $builder = Mockery::mock(Builder::class);

        $builder->shouldReceive('join')
            ->once()->with('bookings', 'bookings.id', '=', 'booking_days.booking_id')
            ->andReturn($builder);

        $builder->shouldReceive('where')
            ->once()->with('booking_days.car_park_id', $carParkId)->andReturn($builder);

        $builder->shouldReceive('whereIn')
            ->once()->with('booking_days.date', $days)->andReturn($builder);

        $builder->shouldReceive('where')
            ->once()->with('bookings.vehicle_reg', strtoupper($vehicle))->andReturn($builder);

        $builder->shouldReceive('whereIn')
            ->once()->with('bookings.status', ['confirmed', 'pending'])->andReturn($builder);

        $builder->shouldReceive('pluck')
            ->once()->with('booking_days.date')
            ->andReturn(collect([
                '2025-09-21',
                '2025-09-22',
                '2025-09-22',
                '2025-09-23',
            ]));

        Mockery::mock('alias:App\Models\BookingDayModel')
            ->shouldReceive('query')
            ->andReturn($builder);

        $repo = new BookingDayRepository;

        $result = $repo->vehicleOverlaps($carParkId, $vehicle, $days);

        $this->assertEqualsCanonicalizing(
            ['2025-09-21', '2025-09-22', '2025-09-23'],
            $result
        );
    }

    public function test_vehicle_overlaps_returns_empty_when_no_days_given(): void
    {
        $repo = new BookingDayRepository;

        $this->assertSame([], $repo->vehicleOverlaps(10, 'KL56NVN', []));
    }
}
