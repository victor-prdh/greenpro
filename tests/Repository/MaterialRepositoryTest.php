<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\Enum\MaterialStatusEnum;
use App\Entity\Material;
use App\Repository\MaterialRepository;
use App\Tests\Fixtures\MaterialCurrentlyRentedFixtures;
use App\Tests\Fixtures\MaterialFixtures;
use App\Tests\Support\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class MaterialRepositoryTest extends KernelTestCase
{
    use FixturesTrait;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->loadFixtures([MaterialFixtures::class]);
    }

    public function testFixturesArePersistedWithTheirStatus(): void
    {
        $repository = static::getContainer()->get(MaterialRepository::class);

        $material = $repository->findOneBy(['reference' => 'MAT-TEST-001']);

        self::assertNotNull($material);
        self::assertSame(MaterialStatusEnum::AVAILABLE, $material->status);
        self::assertSame('45.00', $material->dailyPrice);
        self::assertCount(3, $repository->findAll());
    }

    public function testDefaultStatusIsAvailableForANewlyConstructedMaterial(): void
    {
        $material = new Material();

        self::assertSame(MaterialStatusEnum::AVAILABLE, $material->status);
    }

    public function testCountByStatusGroupsMaterialsByTheirStatus(): void
    {
        $repository = static::getContainer()->get(MaterialRepository::class);

        self::assertSame([
            MaterialStatusEnum::AVAILABLE->value => 1,
            MaterialStatusEnum::RENTED->value => 1,
            MaterialStatusEnum::MAINTENANCE->value => 1,
        ], $repository->countByStatus());
    }

    public function testFindFilteredWithoutFilterReturnsAllMaterials(): void
    {
        $repository = static::getContainer()->get(MaterialRepository::class);

        self::assertCount(3, $repository->findFiltered(false)->getQuery()->getResult());
    }

    public function testFindFilteredOnlyCurrentlyRentedKeepsOnlyMaterialsWithAnOngoingNonCancelledLocation(): void
    {
        $this->loadFixtures([MaterialCurrentlyRentedFixtures::class]);
        $repository = static::getContainer()->get(MaterialRepository::class);

        $references = array_map(
            static fn (Material $material): string => $material->reference,
            $repository->findFiltered(true)->getQuery()->getResult(),
        );

        self::assertSame(['MAT-TEST-CURRENT-001'], $references);
    }

    public function testFindAnomaliesFlagsMaterialsWhoseStatusDisagreesWithTheirLocations(): void
    {
        $this->loadFixtures([MaterialCurrentlyRentedFixtures::class]);
        $repository = static::getContainer()->get(MaterialRepository::class);

        $references = array_map(
            static fn (Material $material): string => $material->reference,
            $repository->findAnomalies()->getQuery()->getResult(),
        );

        sort($references);
        self::assertSame(['MAT-TEST-CURRENT-001', 'MAT-TEST-CURRENT-004'], $references);
        self::assertSame(2, $repository->countAnomalies());
    }
}
