<?php

declare(strict_types=1);

namespace App\Entity\Enum;

enum HistoryTypeEnum: string implements BadgeableEnumInterface
{
    case LOGIN = 'login';
    case LOCATION_STATUS_CHANGED = 'location_status_changed';
    case OTHER = 'other';

    public function trans(): string
    {
        return 'enum.history_type.'.strtolower($this->name);
    }

    public function badgeColor(): string
    {
        return match ($this) {
            self::LOGIN => 'info',
            self::LOCATION_STATUS_CHANGED => 'warning',
            self::OTHER => 'secondary',
        };
    }
}
