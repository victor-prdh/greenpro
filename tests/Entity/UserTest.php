<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\User;
use App\Security\Enum\RoleEnum;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testGetRolesAlwaysIncludesRoleUser(): void
    {
        $user = new User();

        self::assertSame([RoleEnum::USER->value], $user->getRoles());
    }

    public function testGetRolesDeduplicatesRoleUser(): void
    {
        $user = new User();
        $user->setRoles([RoleEnum::USER, RoleEnum::ADMIN]);

        self::assertSame(
            [RoleEnum::USER->value, RoleEnum::ADMIN->value],
            array_values($user->getRoles()),
        );
    }

    public function testSetRolesStoresValuesAndGetRoleEnumsMapsThemBack(): void
    {
        $user = new User();
        $user->setRoles([RoleEnum::MANAGER]);

        self::assertSame(
            [RoleEnum::MANAGER, RoleEnum::USER],
            array_values($user->getRoleEnums()),
        );
    }

    public function testGetUserIdentifierReturnsEmail(): void
    {
        $user = new User();
        $user->email = 'someone@greenpro.fr';

        self::assertSame('someone@greenpro.fr', $user->getUserIdentifier());
    }

    public function testSerializeNeverExposesThePlainPasswordHash(): void
    {
        $user = new User();
        $user->email = 'someone@greenpro.fr';
        $user->setPassword('$2y$13$actualHashValue');

        $serialized = $user->__serialize();

        self::assertSame(hash('crc32c', '$2y$13$actualHashValue'), $serialized["\0App\\Entity\\User\0password"]);
    }
}
