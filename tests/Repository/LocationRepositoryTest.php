<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\Enum\LocationStatusEnum;
use App\Repository\LocationRepository;
use App\Tests\Fixtures\CustomerFixtures;
use App\Tests\Fixtures\LocationFixtures;
use App\Tests\Fixtures\MaterialFixtures;
use App\Tests\Support\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class LocationRepositoryTest extends KernelTestCase
{
    use FixturesTrait;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->loadFixtures([CustomerFixtures::class, MaterialFixtures::class, LocationFixtures::class]);
    }

    public function testFixtureResolvesItsCustomerAndMaterialAssociations(): void
    {
        $repository = static::getContainer()->get(LocationRepository::class);

        $location = $repository->findOneBy(['status' => LocationStatusEnum::CONFIRMED]);

        self::assertNotNull($location);
        self::assertSame('Jardins Alpha', $location->customer?->companyName);
        self::assertCount(1, $location->materials);
        self::assertSame('MAT-TEST-001', $location->materials->first()->reference);
        self::assertSame('315.00', $location->totalPrice);
    }

    public function testCountByStatusGroupsLocationsByTheirStatus(): void
    {
        $repository = static::getContainer()->get(LocationRepository::class);

        self::assertSame([LocationStatusEnum::CONFIRMED->value => 1], $repository->countByStatus());
    }

    public function testCountByStatusValueCountsOnlyTheGivenStatus(): void
    {
        $repository = static::getContainer()->get(LocationRepository::class);

        self::assertSame(1, $repository->countByStatusValue(LocationStatusEnum::CONFIRMED));
        self::assertSame(0, $repository->countByStatusValue(LocationStatusEnum::IN_PROGRESS));
    }

    public function testSumRevenueExcludesCancelledLocations(): void
    {
        $repository = static::getContainer()->get(LocationRepository::class);

        self::assertSame('315.00', $repository->sumRevenue());
    }

    public function testFindActivitySinceReturnsLocationsCreatedAfterTheGivenDate(): void
    {
        $repository = static::getContainer()->get(LocationRepository::class);

        $rows = $repository->findActivitySince(new \DateTimeImmutable('-1 day'));

        self::assertCount(1, $rows);
        self::assertSame('315.00', $rows[0]['totalPrice']);
        self::assertSame(LocationStatusEnum::CONFIRMED, $rows[0]['status']);
    }
}
