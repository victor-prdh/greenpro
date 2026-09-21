<?php

declare(strict_types=1);

namespace App\Entity\Enum;

interface BadgeableEnumInterface
{
    public function trans(): string;

    public function badgeColor(): string;
}
