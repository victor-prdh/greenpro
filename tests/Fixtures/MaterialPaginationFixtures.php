<?php

declare(strict_types=1);

namespace App\Tests\Fixtures;

use App\Entity\Enum\MaterialStatusEnum;
use App\Entity\Material;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Seeds 32 Material rows (more than the Paginator's default limit of 30) so
 * controller-level tests can assert that a second page is reachable.
 */
class MaterialPaginationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 32; ++$i) {
            $material = new Material();
            $material->name = \sprintf('Matériel %02d', $i);
            $material->reference = \sprintf('MAT-PAGE-%03d', $i);
            $material->dailyPrice = '10.00';
            $material->status = MaterialStatusEnum::AVAILABLE;
            $manager->persist($material);
        }

        $manager->flush();
    }
}
