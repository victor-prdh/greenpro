<?php

declare(strict_types=1);

namespace App\Tests\Fixtures;

use App\Entity\Customer;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Small, deterministic stand-in for App\DataFixtures\CustomerFixtures (which
 * seeds 800 randomized rows) so tests get predictable data without the cost.
 */
class CustomerFixtures extends Fixture
{
    public const string REFERENCE_ALPHA = 'customer_alpha';
    public const string REFERENCE_BRAVO = 'customer_bravo';

    public function load(ObjectManager $manager): void
    {
        $alpha = new Customer();
        $alpha->companyName = 'Jardins Alpha';
        $alpha->contactName = 'Alice Dupont';
        $alpha->email = 'alice@jardins-alpha.test';
        $alpha->phone = '0100000001';
        $alpha->address = '1 rue des Fleurs';
        $alpha->postalCode = '75001';
        $alpha->city = 'Paris';
        $manager->persist($alpha);
        $this->addReference(self::REFERENCE_ALPHA, $alpha);

        $bravo = new Customer();
        $bravo->companyName = 'Espaces Verts Bravo';
        $bravo->contactName = 'Bruno Martin';
        $bravo->email = 'bruno@espaces-bravo.test';
        $bravo->phone = '0100000002';
        $bravo->address = '2 avenue des Chênes';
        $bravo->postalCode = '69001';
        $bravo->city = 'Lyon';
        $manager->persist($bravo);
        $this->addReference(self::REFERENCE_BRAVO, $bravo);

        $manager->flush();
    }
}
