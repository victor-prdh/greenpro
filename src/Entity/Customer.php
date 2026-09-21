<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Enum\TableEnum;
use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\UpdatedAtTrait;
use App\Entity\Traits\UuidTrait;
use App\Repository\CustomerRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: CustomerRepository::class)]
#[ORM\Table(name: TableEnum::CUSTOMER->value)]
#[ORM\UniqueConstraint(fields: ['email'])]
#[UniqueEntity(fields: ['email'])]
class Customer
{
    use CreatedAtTrait;
    use UpdatedAtTrait;
    use UuidTrait;

    #[ORM\Column(length: 255)]
    public ?string $companyName = null;

    #[ORM\Column(length: 255)]
    public ?string $contactName = null;

    #[ORM\Column(length: 255)]
    public ?string $email = null;

    #[ORM\Column(length: 255)]
    public ?string $phone = null;

    #[ORM\Column(length: 255)]
    public ?string $address = null;

    #[ORM\Column(length: 10)]
    public ?string $postalCode = null;

    #[ORM\Column(length: 150)]
    public ?string $city = null;
}
