<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\DataFixtures\UserFixtures;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Loads a handful of test-only Doctrine fixtures (App\Tests\Fixtures\*) inside
 * a KernelTestCase/WebTestCase, reusing the same doctrine-fixtures-bundle
 * mechanics as `doctrine:fixtures:load`, purging the schema each time so
 * tests stay independent of one another.
 *
 * @method static \Symfony\Component\DependencyInjection\ContainerInterface getContainer()
 */
trait FixturesTrait
{
    /**
     * @param list<class-string<Fixture>> $fixtureClasses
     */
    protected function loadFixtures(array $fixtureClasses): void
    {
        $container = static::getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);

        $loader = new Loader();
        foreach ($fixtureClasses as $fixtureClass) {
            $loader->addFixture($this->instantiateFixture($fixtureClass, $container));
        }

        $executor = new ORMExecutor($entityManager, new ORMPurger($entityManager));
        $executor->execute($loader->getFixtures());
    }

    private function instantiateFixture(string $fixtureClass, object $container): Fixture
    {
        if (UserFixtures::class === $fixtureClass) {
            return new UserFixtures($container->get(UserPasswordHasherInterface::class));
        }

        return new $fixtureClass();
    }
}
