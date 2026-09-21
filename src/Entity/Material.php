<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Enum\MaterialStatusEnum;
use App\Entity\Enum\TableEnum;
use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\UpdatedAtTrait;
use App\Entity\Traits\UuidTrait;
use App\Repository\MaterialRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: MaterialRepository::class)]
#[ORM\Table(name: TableEnum::MATERIAL->value)]
#[ORM\UniqueConstraint(fields: ['reference'])]
#[UniqueEntity(fields: ['reference'])]
class Material
{
    use CreatedAtTrait;
    use UpdatedAtTrait;
    use UuidTrait;

    #[ORM\Column(length: 255)]
    public ?string $name = null;

    #[ORM\Column(length: 255)]
    public ?string $reference = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    public ?string $dailyPrice = null;

    #[ORM\Column(enumType: MaterialStatusEnum::class)]
    public MaterialStatusEnum $status = MaterialStatusEnum::AVAILABLE;
}
