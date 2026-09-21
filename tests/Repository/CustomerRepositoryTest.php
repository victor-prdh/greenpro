<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\Customer;
use App\Repository\CustomerRepository;
use App\Tests\Fixtures\CustomerFixtures;
use App\Tests\Support\FixturesTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CustomerRepositoryTest extends KernelTestCase
{
    use FixturesTrait;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->loadFixtures([CustomerFixtures::class]);
    }

    public function testFixturesArePersistedAndFindable(): void
    {
        $repository = static::getContainer()->get(CustomerRepository::class);

        $customer = $repository->findOneBy(['email' => 'alice@jardins-alpha.test']);

        self::assertNotNull($customer);
        self::assertSame('Jardins Alpha', $customer->companyName);
        self::assertSame('Paris', $customer->city);
        self::assertCount(2, $repository->findAll());
    }

    public function testCreatedAtIsAssignedByTheDatabaseOnInsert(): void
    {
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $customer = new Customer();
        $customer->companyName = 'Nouvelle Entreprise';
        $customer->contactName = 'Claire Nouveau';
        $customer->email = 'claire@nouvelle-entreprise.test';
        $customer->phone = '0100000099';
        $customer->address = '3 impasse des Lilas';
        $customer->postalCode = '13001';
        $customer->city = 'Marseille';

        $entityManager->persist($customer);
        $entityManager->flush();
        $entityManager->refresh($customer);

        self::assertNotNull($customer->createdAt);
    }

    public function testDuplicateEmailViolatesTheUniqueConstraint(): void
    {
        $validator = static::getContainer()->get(ValidatorInterface::class);

        $duplicate = new Customer();
        $duplicate->companyName = 'Autre Société';
        $duplicate->contactName = 'Someone Else';
        $duplicate->email = 'alice@jardins-alpha.test';
        $duplicate->phone = '0100000098';
        $duplicate->address = '4 rue du Doublon';
        $duplicate->postalCode = '75002';
        $duplicate->city = 'Paris';

        $violations = $validator->validate($duplicate);

        self::assertGreaterThan(0, \count($violations));
    }
}
