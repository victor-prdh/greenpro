<?php

declare(strict_types=1);

namespace App\Tests\Fixtures;

use App\Entity\Enum\MaterialStatusEnum;
use App\Entity\Material;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Small, deterministic stand-in for App\DataFixtures\MaterialFixtures (which
 * seeds 5000 randomized rows) so tests get predictable data without the cost.
 */
class MaterialFixtures extends Fixture
{
    public const string REFERENCE_AVAILABLE = 'material_available';
    public const string REFERENCE_RENTED = 'material_rented';
    public const string REFERENCE_MAINTENANCE = 'material_maintenance';

    public function load(ObjectManager $manager): void
    {
        $available = new Material();
        $available->name = 'Tondeuse autoportée Husqvarna';
        $available->reference = 'MAT-TEST-001';
        $available->dailyPrice = '45.00';
        $available->status = MaterialStatusEnum::AVAILABLE;
        $manager->persist($available);
        $this->addReference(self::REFERENCE_AVAILABLE, $available);

        $rented = new Material();
        $rented->name = 'Nettoyeur haute pression Kärcher';
        $rented->reference = 'MAT-TEST-002';
        $rented->dailyPrice = '20.00';
        $rented->status = MaterialStatusEnum::RENTED;
        $manager->persist($rented);
        $this->addReference(self::REFERENCE_RENTED, $rented);

        $maintenance = new Material();
        $maintenance->name = 'Groupe électrogène SDMO';
        $maintenance->reference = 'MAT-TEST-003';
        $maintenance->dailyPrice = '60.00';
        $maintenance->status = MaterialStatusEnum::MAINTENANCE;
        $manager->persist($maintenance);
        $this->addReference(self::REFERENCE_MAINTENANCE, $maintenance);

        $manager->flush();
    }
}
