<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Location;
use App\Entity\Material;
use PHPUnit\Framework\TestCase;

class LocationTest extends TestCase
{
    public function testAddMaterialAppendsIt(): void
    {
        $location = new Location();
        $material = new Material();

        $location->addMaterial($material);

        self::assertCount(1, $location->materials);
        self::assertTrue($location->materials->contains($material));
    }

    public function testAddMaterialIsIdempotent(): void
    {
        $location = new Location();
        $material = new Material();

        $location->addMaterial($material);
        $location->addMaterial($material);

        self::assertCount(1, $location->materials);
    }

    public function testRemoveMaterialTakesItOut(): void
    {
        $location = new Location();
        $material = new Material();
        $location->addMaterial($material);

        $location->removeMaterial($material);

        self::assertCount(0, $location->materials);
    }
}
