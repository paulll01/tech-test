<?php

namespace Tests\Unit\Services;

use App\Models\CarParkModel;
use App\Repositories\BookingDay\IBookingDayRepository;
use App\Services\BookingDay\BookingDayService;
use App\ValueObjects\Availability\DateRangeVO;
use Mockery;
use Tests\TestCase;

class BookingDayServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_get_booked_per_day_delegates_to_repository_and_returns_map(): void
    {
        /** @var IBookingDayRepository&\Mockery\MockInterface $repoMock */
        $repoMock = Mockery::mock(IBookingDayRepository::class);
        $service = new BookingDayService($repoMock);

        /** @var CarParkModel $carPark */
        $carPark = CarParkModel::factory()->make([
            'id' => 1,
            'name' => 'T1',
            'capacity' => 10,
            'default_weekday_price' => 30.00,
            'default_weekend_price' => 40.00,
        ]);

        // 3 days range
        $range = DateRangeVO::fromStrings('2025-10-01', '2025-10-03');
        $expectedDays = ['2025-10-01', '2025-10-02', '2025-10-03'];

        $repoMock->shouldReceive('countBookedPerDay')
            ->once()
            ->with(1, $expectedDays)
            ->andReturn([
                '2025-10-01' => 2,
                '2025-10-03' => 5,
            ]);

        $result = $service->getBookedPerDay($carPark, $range);

        $this->assertSame([
            '2025-10-01' => 2,
            '2025-10-03' => 5,
        ], $result);
    }

    public function test_get_booked_per_day_with_empty_range_returns_empty_array(): void
    {
        /** @var IBookingDayRepository&\Mockery\MockInterface $repoMock */
        $repoMock = Mockery::mock(IBookingDayRepository::class);
        $service = new BookingDayService($repoMock);

        /** @var CarParkModel $carPark */
        $carPark = CarParkModel::factory()->make(['id' => 3]);

        $range = DateRangeVO::fromStrings('2025-10-01', '2025-10-01');

        $repoMock->shouldReceive('countBookedPerDay')
            ->once()
            ->with(3, ['2025-10-01'])
            ->andReturn([]);

        $result = $service->getBookedPerDay($carPark, $range);

        $this->assertSame([], $result);
    }
}
