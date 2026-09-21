<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Customer;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class CustomerFixtures extends Fixture
{
    public const int COUNT = 800;

    private Generator $faker;

    public function load(ObjectManager $manager): void
    {
        $this->faker = Factory::create('fr_FR');

        for ($i = 1; $i <= self::COUNT; ++$i) {
            $customer = new Customer();
            $customer->companyName = $this->faker->unique()->company();
            $customer->contactName = $this->faker->name();
            $customer->email = $this->faker->unique()->companyEmail();
            $customer->phone = $this->faker->phoneNumber();
            $customer->address = $this->faker->streetAddress();
            $customer->postalCode = $this->faker->postcode();
            $customer->city = $this->faker->city();

            $manager->persist($customer);
            $this->addReference('customer_'.$i, $customer);

            if (0 === $i % 200) {
                $manager->flush();
            }
        }

        $manager->flush();
    }
}
