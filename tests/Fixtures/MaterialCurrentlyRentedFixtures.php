<?php

declare(strict_types=1);

namespace App\Tests\Fixtures;

use App\Entity\Customer;
use App\Entity\Enum\LocationStatusEnum;
use App\Entity\Enum\MaterialStatusEnum;
use App\Entity\Location;
use App\Entity\Material;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Covers the cases the "currently rented" material filter must tell apart:
 * an ongoing, non-cancelled location is the only thing that counts, whatever
 * the Material.status field says.
 */
class MaterialCurrentlyRentedFixtures extends Fixture implements DependentFixtureInterface
{
    public const string REFERENCE_ONGOING = 'material_ongoing_location';
    public const string REFERENCE_ONGOING_CANCELLED = 'material_ongoing_cancelled_location';
    public const string REFERENCE_PAST = 'material_past_location';
    public const string REFERENCE_RENTED_STATUS_NO_LOCATION = 'material_rented_status_no_location';

    public function getDependencies(): array
    {
        return [CustomerFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        $customer = $this->getReference(CustomerFixtures::REFERENCE_ALPHA, Customer::class);

        $ongoing = new Material();
        $ongoing->name = 'Bétonnière Altrad';
        $ongoing->reference = 'MAT-TEST-CURRENT-001';
        $ongoing->dailyPrice = '30.00';
        $ongoing->status = MaterialStatusEnum::AVAILABLE;
        $manager->persist($ongoing);
        $this->addReference(self::REFERENCE_ONGOING, $ongoing);

        $ongoingLocation = new Location();
        $ongoingLocation->startAt = new \DateTimeImmutable('-1 day');
        $ongoingLocation->endAt = new \DateTimeImmutable('+1 day');
        $ongoingLocation->status = LocationStatusEnum::IN_PROGRESS;
        $ongoingLocation->totalPrice = '60.00';
        $ongoingLocation->customer = $customer;
        $ongoingLocation->addMaterial($ongoing);
        $manager->persist($ongoingLocation);

        $ongoingCancelled = new Material();
        $ongoingCancelled->name = 'Plateforme élévatrice';
        $ongoingCancelled->reference = 'MAT-TEST-CURRENT-002';
        $ongoingCancelled->dailyPrice = '80.00';
        $ongoingCancelled->status = MaterialStatusEnum::AVAILABLE;
        $manager->persist($ongoingCancelled);
        $this->addReference(self::REFERENCE_ONGOING_CANCELLED, $ongoingCancelled);

        $cancelledLocation = new Location();
        $cancelledLocation->startAt = new \DateTimeImmutable('-1 day');
        $cancelledLocation->endAt = new \DateTimeImmutable('+1 day');
        $cancelledLocation->status = LocationStatusEnum::CANCELLED;
        $cancelledLocation->totalPrice = '160.00';
        $cancelledLocation->customer = $customer;
        $cancelledLocation->addMaterial($ongoingCancelled);
        $manager->persist($cancelledLocation);

        $past = new Material();
        $past->name = 'Compresseur Atlas Copco';
        $past->reference = 'MAT-TEST-CURRENT-003';
        $past->dailyPrice = '25.00';
        $past->status = MaterialStatusEnum::AVAILABLE;
        $manager->persist($past);
        $this->addReference(self::REFERENCE_PAST, $past);

        $pastLocation = new Location();
        $pastLocation->startAt = new \DateTimeImmutable('-10 days');
        $pastLocation->endAt = new \DateTimeImmutable('-3 days');
        $pastLocation->status = LocationStatusEnum::FINISHED;
        $pastLocation->totalPrice = '75.00';
        $pastLocation->customer = $customer;
        $pastLocation->addMaterial($past);
        $manager->persist($pastLocation);

        $rentedStatusNoLocation = new Material();
        $rentedStatusNoLocation->name = 'Perforateur Hilti';
        $rentedStatusNoLocation->reference = 'MAT-TEST-CURRENT-004';
        $rentedStatusNoLocation->dailyPrice = '15.00';
        $rentedStatusNoLocation->status = MaterialStatusEnum::RENTED;
        $manager->persist($rentedStatusNoLocation);
        $this->addReference(self::REFERENCE_RENTED_STATUS_NO_LOCATION, $rentedStatusNoLocation);

        $manager->flush();
    }
}
