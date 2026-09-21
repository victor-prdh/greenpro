<?php

declare(strict_types=1);

namespace App\Tests\Fixtures;

use App\Entity\Customer;
use App\Entity\Enum\LocationStatusEnum;
use App\Entity\Location;
use App\Entity\Material;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Small, deterministic stand-in for App\DataFixtures\LocationFixtures (which
 * seeds 4000 randomized rows) so tests get predictable data without the cost.
 */
class LocationFixtures extends Fixture implements DependentFixtureInterface
{
    public const string REFERENCE_CONFIRMED = 'location_confirmed';

    public function getDependencies(): array
    {
        return [
            CustomerFixtures::class,
            MaterialFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $location = new Location();
        $location->startAt = new \DateTimeImmutable('2026-10-01');
        $location->endAt = new \DateTimeImmutable('2026-10-08');
        $location->status = LocationStatusEnum::CONFIRMED;
        $location->totalPrice = '315.00';
        $location->customer = $this->getReference(CustomerFixtures::REFERENCE_ALPHA, Customer::class);
        $location->addMaterial($this->getReference(MaterialFixtures::REFERENCE_AVAILABLE, Material::class));

        $manager->persist($location);
        $this->addReference(self::REFERENCE_CONFIRMED, $location);

        $manager->flush();
    }
}
