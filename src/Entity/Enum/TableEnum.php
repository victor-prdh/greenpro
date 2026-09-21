<?php

declare(strict_types=1);

namespace App\Entity\Enum;

enum TableEnum: string
{
    case USER = 'user';
    case CUSTOMER = 'customer';
    case LOCATION = 'location';
    case MATERIAL = 'material';
    case LOCATION_MATERIAL = 'location_material';
}
