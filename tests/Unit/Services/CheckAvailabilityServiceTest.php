<?php

namespace Tests\Unit\Services;

use App\DTO\Availability\CheckAvailabilityDTO;
use App\DTO\Availability\CheckAvailabilityResponseDTO;
use App\DTO\Pricing\PricingQuoteDTO;
use App\Models\CarParkModel;
use App\Services\ApplicationServices\Availability\CheckAvailabilityService;
use App\Services\BookingDay\IBookingDayService;
use App\Services\Pricing\IQuotePricingService;
use Mockery;
use Tests\TestCase;

class CheckAvailabilityServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_handle_builds_days_and_availability_and_total_price_from_dependencies(): void
    {
        // Mocks
        $bookingDayServiceMock = Mockery::mock(IBookingDayService::class);
        $pricingServiceMock = Mockery::mock(IQuotePricingService::class);

        $service = new CheckAvailabilityService(
            bookingDayService: $bookingDayServiceMock,
            pricingService: $pricingServiceMock
        );

        /** @var \App\Models\CarParkModel $carPark */
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

        $bookingDayServiceMock->shouldReceive('getBookedPerDay')
            ->once()
            ->withArgs(function ($passedCarPark, $range) use ($carPark) {
                return $passedCarPark->is($carPark)
                    && method_exists($range, 'days')
                    && $range->days() === [
                        '2025-10-01',
                        '2025-10-02',
                        '2025-10-03',
                        '2025-10-04',
                        '2025-10-05',
                    ];
            })
            ->andReturn([
                '2025-10-01' => 0,
                '2025-10-02' => 10,
                '2025-10-03' => 6,
            ]);

        $pricingDays = [
            '2025-10-01' => ['price' => 30.00, 'is_weekend' => false, 'source' => 'default'],
            '2025-10-02' => ['price' => 30.00, 'is_weekend' => false, 'source' => 'default'],
            '2025-10-03' => ['price' => 30.00, 'is_weekend' => false, 'source' => 'default'],
            '2025-10-04' => ['price' => 40.00, 'is_weekend' => true,  'source' => 'default'],
            '2025-10-05' => ['price' => 40.00, 'is_weekend' => true,  'source' => 'default'],
        ];
        $quoteTotal = 170.00;
        $quoteCurrency = 'GBP';

        $quoteDTO = new PricingQuoteDTO(
            $pricingDays,
            $quoteTotal,
            $quoteCurrency
        );

        $pricingServiceMock->shouldReceive('quote')
            ->once()
            ->andReturn($quoteDTO);

        $result = $service->handle($carPark, $dto);

        $this->assertInstanceOf(CheckAvailabilityResponseDTO::class, $result);

        $expected = [
            'car_park' => 'T1',
            'capacity' => 10,
            'days' => [
                ['date' => '2025-10-01', 'available' => 10, 'price' => 30.00, 'is_weekend' => false, 'price_source' => 'default'],
                ['date' => '2025-10-02', 'available' => 0,  'price' => 30.00, 'is_weekend' => false, 'price_source' => 'default'],
                ['date' => '2025-10-03', 'available' => 4,  'price' => 30.00, 'is_weekend' => false, 'price_source' => 'default'],
                ['date' => '2025-10-04', 'available' => 10, 'price' => 40.00, 'is_weekend' => true,  'price_source' => 'default'],
                ['date' => '2025-10-05', 'available' => 10, 'price' => 40.00, 'is_weekend' => true,  'price_source' => 'default'],
            ],
            'availability' => false,
            'total_price' => number_format(170, 2, '.', ''),
            'currency' => 'GBP',
        ];

        $this->assertSame($expected, $result->toArray());
    }
}
