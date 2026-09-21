<?php

declare(strict_types=1);

namespace App\Entity\Enum;

enum MaterialStatusEnum: string implements BadgeableEnumInterface
{
    case AVAILABLE = 'available';
    case RENTED = 'rented';
    case MAINTENANCE = 'maintenance';
    case RETIRED = 'retired';

    public function trans(): string
    {
        return 'enum.material_status.'.strtolower($this->name);
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::AVAILABLE => 'success',
            self::RENTED => 'primary',
            self::MAINTENANCE => 'warning',
            self::RETIRED => 'secondary',
        };
    }
}
