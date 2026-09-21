<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Enum\LocationStatusEnum;
use App\Entity\Enum\MaterialStatusEnum;
use App\Service\DashboardStatsProvider;
use App\Tests\Fixtures\CustomerFixtures;
use App\Tests\Fixtures\LocationFixtures;
use App\Tests\Fixtures\MaterialFixtures;
use App\Tests\Support\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DashboardStatsProviderTest extends KernelTestCase
{
    use FixturesTrait;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->loadFixtures([CustomerFixtures::class, MaterialFixtures::class, LocationFixtures::class]);
    }

    public function testGetStatsAggregatesCountsAndRevenueFromTheFixtures(): void
    {
        $provider = static::getContainer()->get(DashboardStatsProvider::class);

        $stats = $provider->getStats();

        self::assertSame(2, $stats->customerCount);
        self::assertSame(3, $stats->materialCount);
        self::assertSame(0, $stats->activeLocationCount);
        self::assertSame(0, $stats->anomalyLocationCount);
        self::assertSame(1, $stats->anomalyMaterialCount);
        self::assertSame('315.00', $stats->totalRevenue);
        self::assertSame([
            MaterialStatusEnum::AVAILABLE->value => 1,
            MaterialStatusEnum::RENTED->value => 1,
            MaterialStatusEnum::MAINTENANCE->value => 1,
        ], $stats->materialsByStatus);
        self::assertSame([LocationStatusEnum::CONFIRMED->value => 1], $stats->locationsByStatus);
    }

    public function testGetStatsBucketsActivityOverTheLastSixMonths(): void
    {
        $provider = static::getContainer()->get(DashboardStatsProvider::class);

        $stats = $provider->getStats();

        self::assertCount(6, $stats->locationsPerMonth);

        $currentMonth = $stats->locationsPerMonth[array_key_last($stats->locationsPerMonth)];
        self::assertSame(1, $currentMonth->locationCount);
        self::assertSame('315.00', $currentMonth->revenue);

        $totalLocations = array_sum(array_map(
            static fn ($month) => $month->locationCount,
            $stats->locationsPerMonth,
        ));
        self::assertSame(1, $totalLocations);
    }
}
