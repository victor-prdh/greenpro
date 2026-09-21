<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use App\Security\Enum\RoleEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $this->createUser($manager, 'admin@greenpro.fr', [RoleEnum::ADMIN]);
        $this->createUser($manager, 'manager@greenpro.fr', [RoleEnum::MANAGER]);

        $manager->flush();
    }

    /**
     * @param list<RoleEnum> $roles
     */
    private function createUser(ObjectManager $manager, string $email, array $roles): User
    {
        $user = new User();
        $user->email = $email;
        $user->setRoles($roles);
        $user->setPassword($this->passwordHasher->hashPassword($user, 'greenpro'));

        $manager->persist($user);

        return $user;
    }
}
