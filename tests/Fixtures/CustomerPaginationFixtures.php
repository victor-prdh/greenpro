<?php

declare(strict_types=1);

namespace App\Tests\Fixtures;

use App\Entity\Customer;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Seeds 32 Customer rows (more than the Paginator's default limit of 30) so
 * controller-level tests can assert that a second page is reachable.
 */
class CustomerPaginationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 1; $i <= 32; ++$i) {
            $customer = new Customer();
            $customer->companyName = \sprintf('Société %02d', $i);
            $customer->contactName = \sprintf('Contact %02d', $i);
            $customer->email = \sprintf('contact-%02d@societe.test', $i);
            $customer->phone = '0100000000';
            $customer->address = '1 rue des Tests';
            $customer->postalCode = '75000';
            $customer->city = 'Paris';
            $manager->persist($customer);
        }

        $manager->flush();
    }
}
