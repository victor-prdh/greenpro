<?php

declare(strict_types=1);

namespace App\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait CreatedAtTrait
{
    #[ORM\Column(
        type: Types::DATETIME_MUTABLE,
        insertable: false,
        updatable: false,
        options: ['default' => 'CURRENT_TIMESTAMP'],
        generated: 'INSERT'
    )]
    public ?\DateTime $createdAt = null;
}
