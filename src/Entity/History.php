<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Enum\HistoryTypeEnum;
use App\Entity\Enum\TableEnum;
use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\UuidTrait;
use App\Repository\HistoryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HistoryRepository::class)]
#[ORM\Table(name: TableEnum::HISTORY->value)]
class History
{
    use CreatedAtTrait, UuidTrait;

    #[ORM\Column(type: Types::TEXT)]
    public ?string $message = null;

    #[ORM\Column(enumType: HistoryTypeEnum::class)]
    public ?HistoryTypeEnum $type = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'author_uuid', referencedColumnName: 'uuid', nullable: true)]
    public ?User $author = null;
}
