<?php

declare(strict_types=1);

namespace App\Tests\Fixtures;

use App\Entity\Enum\MaterialStatusEnum;
use App\Entity\Material;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Seeds seven Material rows with predictable references (MAT-PAGE-001..007)
 * so App\Pagination\Paginator tests can assert on page boundaries.
 */
class PaginatorFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 7; ++$i) {
            $material = new Material();
            $material->name = \sprintf('Matériel %d', $i);
            $material->reference = \sprintf('MAT-PAGE-%03d', $i);
            $material->dailyPrice = '10.00';
            $material->status = MaterialStatusEnum::AVAILABLE;
            $manager->persist($material);
        }

        $manager->flush();
    }
}
