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
 * Locations covering both anomaly cases (ongoing but not in-progress, past but
 * not finished) plus one healthy location, so anomaly detection tests have
 * predictable data without depending on a fixed calendar date.
 */
class LocationAnomalyFixtures extends Fixture implements DependentFixtureInterface
{
    public const string REFERENCE_ONGOING_ANOMALY = 'location_ongoing_anomaly';
    public const string REFERENCE_PAST_ANOMALY = 'location_past_anomaly';
    public const string REFERENCE_HEALTHY = 'location_healthy';

    public function getDependencies(): array
    {
        return [
            CustomerFixtures::class,
            MaterialFixtures::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $material = $this->getReference(MaterialFixtures::REFERENCE_AVAILABLE, Material::class);

        $ongoingAnomaly = new Location();
        $ongoingAnomaly->startAt = new \DateTimeImmutable('-2 days');
        $ongoingAnomaly->endAt = new \DateTimeImmutable('+2 days');
        $ongoingAnomaly->status = LocationStatusEnum::CONFIRMED;
        $ongoingAnomaly->totalPrice = '200.00';
        $ongoingAnomaly->customer = $this->getReference(CustomerFixtures::REFERENCE_ALPHA, Customer::class);
        $ongoingAnomaly->addMaterial($material);
        $manager->persist($ongoingAnomaly);
        $this->addReference(self::REFERENCE_ONGOING_ANOMALY, $ongoingAnomaly);

        $pastAnomaly = new Location();
        $pastAnomaly->startAt = new \DateTimeImmutable('-10 days');
        $pastAnomaly->endAt = new \DateTimeImmutable('-3 days');
        $pastAnomaly->status = LocationStatusEnum::IN_PROGRESS;
        $pastAnomaly->totalPrice = '150.00';
        $pastAnomaly->customer = $this->getReference(CustomerFixtures::REFERENCE_BRAVO, Customer::class);
        $pastAnomaly->addMaterial($material);
        $manager->persist($pastAnomaly);
        $this->addReference(self::REFERENCE_PAST_ANOMALY, $pastAnomaly);

        $healthy = new Location();
        $healthy->startAt = new \DateTimeImmutable('-10 days');
        $healthy->endAt = new \DateTimeImmutable('-3 days');
        $healthy->status = LocationStatusEnum::FINISHED;
        $healthy->totalPrice = '150.00';
        $healthy->customer = $this->getReference(CustomerFixtures::REFERENCE_BRAVO, Customer::class);
        $healthy->addMaterial($material);
        $manager->persist($healthy);
        $this->addReference(self::REFERENCE_HEALTHY, $healthy);

        $manager->flush();
    }
}
