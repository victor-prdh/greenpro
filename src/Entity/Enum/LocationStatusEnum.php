<?php

declare(strict_types=1);

namespace App\Entity\Enum;

enum LocationStatusEnum: string implements BadgeableEnumInterface
{
    case DRAFT = 'draft';
    case CONFIRMED = 'confirmed';
    case IN_PROGRESS = 'in-progress';
    case FINISHED = 'finished';
    case CANCELLED = 'cancelled';

    public function trans(): string
    {
        return 'enum.location_status.'.strtolower($this->name);
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::DRAFT => 'secondary',
            self::CONFIRMED => 'info',
            self::IN_PROGRESS => 'primary',
            self::FINISHED => 'success',
            self::CANCELLED => 'danger',
        };
    }
}
