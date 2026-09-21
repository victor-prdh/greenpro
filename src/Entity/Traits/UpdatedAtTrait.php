<?php

declare(strict_types=1);

namespace App\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait UpdatedAtTrait
{
    #[ORM\Column(
        type: Types::DATETIME_MUTABLE,
        nullable: true,
        insertable: false,
        updatable: false,
        columnDefinition: 'DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP',
        generated: 'ALWAYS',
    )]
    public ?\DateTime $updatedAt = null;
}
