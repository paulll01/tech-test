<?php

namespace Tests\Unit\Repositories;

use App\DTO\Pricing\SeasonDayPriceDTO;
use App\Models\CarParkModel;
use App\Repositories\PricingSeason\PricingSeasonRepository;
use App\ValueObjects\Availability\DateRangeVO;
use Illuminate\Database\Eloquent\Builder;
use Mockery;
use Tests\TestCase;

class PricingSeasonRepositoryTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function mockSeasonQueryReturning(array $rows)
    {
        /** @var Builder&\Mockery\MockInterface $builder */
        $builder = Mockery::mock(Builder::class);

        $builder->shouldReceive('where')
            ->withAnyArgs()
            ->andReturnUsing(function (...$args) use ($builder) {
                if (isset($args[0]) && $args[0] instanceof \Closure) {
                    ($args[0])($builder);
                }

                return $builder;
            });

        $builder->shouldReceive('whereBetween')->withAnyArgs()->andReturn($builder);
        $builder->shouldReceive('orWhereBetween')->withAnyArgs()->andReturn($builder);

        $builder->shouldReceive('orWhere')
            ->withAnyArgs()
            ->andReturnUsing(function (...$args) use ($builder) {
                if (isset($args[0]) && $args[0] instanceof \Closure) {
                    ($args[0])($builder);
                }

                return $builder;
            });

        $builder->shouldReceive('orderBy')->with('start_date')->andReturn($builder);

        $builder->shouldReceive('get')
            ->once()
            ->with(['name', 'start_date', 'end_date', 'weekday_price', 'weekend_price'])
            ->andReturn(collect($rows));

        Mockery::mock('alias:App\Models\PricingSeasonModel')
            ->shouldReceive('query')
            ->andReturn($builder);

        return $builder;
    }

    public function test_season_by_date_returns_empty(): void
    {
        /** @var CarParkModel $carPark */
        $carPark = CarParkModel::factory()->make(['id' => 10, 'name' => 'T1']);

        // no seasons returned
        $this->mockSeasonQueryReturning([]);

        $range = DateRangeVO::fromStrings('2025-09-21', '2025-09-23');

        $map = (new PricingSeasonRepository)->seasonByDate($carPark, $range);

        $this->assertSame([], $map);
    }

    public function test_season_by_date_maps_single_covering_season_for_each_day(): void
    {
        /** @var CarParkModel $carPark */
        $carPark = CarParkModel::factory()->make(['id' => 10, 'name' => 'T1']);

        // season covering entire month
        $season = (object) [
            'name' => 'September',
            'start_date' => '2025-09-01',
            'end_date' => '2025-09-30',
            'weekday_price' => 35.0,
            'weekend_price' => 45.0,
        ];

        $this->mockSeasonQueryReturning([$season]);

        $range = DateRangeVO::fromStrings('2025-09-21', '2025-09-23');

        /** @var array<string,SeasonDayPriceDTO> $map */
        $map = (new PricingSeasonRepository)->seasonByDate($carPark, $range);

        foreach (['2025-09-21', '2025-09-22', '2025-09-23'] as $d) {
            $this->assertArrayHasKey($d, $map);
            $this->assertInstanceOf(SeasonDayPriceDTO::class, $map[$d]);
            $this->assertSame('September', $map[$d]->seasonName);
            $this->assertSame(35.0, $map[$d]->weekdayPrice);
            $this->assertSame(45.0, $map[$d]->weekendPrice);
        }
    }

    public function test_season_by_date_handles_multiple_seasons_and_picks_correct_one_per_day(): void
    {
        /** @var CarParkModel $carPark */
        $carPark = CarParkModel::factory()->make(['id' => 10, 'name' => 'T1']);

        // 2 seasons around the requested range
        $early = (object) [
            'name' => 'Early Sept',
            'start_date' => '2025-09-20',
            'end_date' => '2025-09-21',
            'weekday_price' => 20.0,
            'weekend_price' => 25.0,
        ];
        $late = (object) [
            'name' => 'Late Sept',
            'start_date' => '2025-09-22',
            'end_date' => '2025-09-23',
            'weekday_price' => 40.0,
            'weekend_price' => 50.0,
        ];

        $this->mockSeasonQueryReturning([$early, $late]);

        $range = DateRangeVO::fromStrings('2025-09-21', '2025-09-23');

        /** @var array<string,SeasonDayPriceDTO> $map */
        $map = (new PricingSeasonRepository)->seasonByDate($carPark, $range);

        $this->assertArrayHasKey('2025-09-21', $map);
        $this->assertSame('Early Sept', $map['2025-09-21']->seasonName);
        $this->assertSame(20.0, $map['2025-09-21']->weekdayPrice);
        $this->assertSame(25.0, $map['2025-09-21']->weekendPrice);

        $this->assertArrayHasKey('2025-09-22', $map);
        $this->assertSame('Late Sept', $map['2025-09-22']->seasonName);
        $this->assertSame(40.0, $map['2025-09-22']->weekdayPrice);
        $this->assertSame(50.0, $map['2025-09-22']->weekendPrice);

        $this->assertArrayHasKey('2025-09-23', $map);
        $this->assertSame('Late Sept', $map['2025-09-23']->seasonName);
        $this->assertSame(40.0, $map['2025-09-23']->weekdayPrice);
        $this->assertSame(50.0, $map['2025-09-23']->weekendPrice);
    }
}
