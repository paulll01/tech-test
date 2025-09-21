<?php

namespace Tests\Unit\Services;

use App\DTO\Pricing\PricingQuoteDTO;
use App\DTO\Pricing\SeasonDayPriceDTO;
use App\Models\CarParkModel;
use App\Repositories\PricingSeason\IPricingSeasonRepository;
use App\Services\Pricing\QuotePricingService;
use App\ValueObjects\Availability\DateRangeVO;
use Mockery;
use Tests\TestCase;

class QuotePricingServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_quote_uses_default_prices_when_no_season_found(): void
    {
        $repoMock = Mockery::mock(IPricingSeasonRepository::class);
        $service = new QuotePricingService($repoMock);

        /** @var \App\Models\CarParkModel $carPark */
        $carPark = CarParkModel::factory()->make([
            'id' => 1,
            'name' => 'T1',
            'capacity' => 10,
            'default_weekday_price' => 30.00,
            'default_weekend_price' => 40.00,
        ]);

        $range = DateRangeVO::fromStrings('2025-10-01', '2025-10-03');

        $repoMock->shouldReceive('seasonByDate')
            ->once()
            ->with($carPark, $range)
            ->andReturn([]);

        $result = $service->quote($carPark, $range);

        $this->assertInstanceOf(PricingQuoteDTO::class, $result);

        $expectedDays = [
            '2025-10-01' => ['price' => 30.00, 'source' => 'default', 'is_weekend' => false],
            '2025-10-02' => ['price' => 30.00, 'source' => 'default', 'is_weekend' => false],
            '2025-10-03' => ['price' => 30.00, 'source' => 'default', 'is_weekend' => false],
        ];

        $this->assertSame($expectedDays, $result->days);
        $this->assertSame(90.00, $result->total);
        $this->assertSame('GBP', $result->currency);
    }

    public function test_quote_overrides_with_season_prices(): void
    {
        $repoMock = Mockery::mock(IPricingSeasonRepository::class);
        $service = new QuotePricingService($repoMock);

        /** @var \App\Models\CarParkModel $carPark */
        $carPark = CarParkModel::factory()->make([
            'id' => 2,
            'name' => 'T2',
            'capacity' => 20,
            'default_weekday_price' => 25.00,
            'default_weekend_price' => 35.00,
        ]);

        $range = DateRangeVO::fromStrings('2025-10-04', '2025-10-05');

        $seasonStub = new SeasonDayPriceDTO(
            seasonName: 'Autumn Special',
            weekdayPrice: 10.00,
            weekendPrice: 15.00,
        );

        $repoMock->shouldReceive('seasonByDate')
            ->once()
            ->with($carPark, $range)
            ->andReturn([
                '2025-10-04' => $seasonStub,
                '2025-10-05' => $seasonStub,
            ]);

        $result = $service->quote($carPark, $range);

        $expectedDays = [
            '2025-10-04' => ['price' => 15.00, 'source' => 'season:Autumn Special', 'is_weekend' => true],
            '2025-10-05' => ['price' => 15.00, 'source' => 'season:Autumn Special', 'is_weekend' => true],
        ];

        $this->assertSame($expectedDays, $result->days);
        $this->assertSame(30.00, $result->total);
        $this->assertSame('GBP', $result->currency);
    }
}
