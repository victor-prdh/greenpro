<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Enum\LocationStatusEnum;
use App\Entity\Enum\TableEnum;
use App\Entity\Traits\CreatedAtTrait;
use App\Entity\Traits\UpdatedAtTrait;
use App\Entity\Traits\UuidTrait;
use App\Repository\LocationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LocationRepository::class)]
#[ORM\Table(name: TableEnum::LOCATION->value)]
class Location
{
    use CreatedAtTrait, UpdatedAtTrait, UuidTrait {
        UuidTrait::__construct as private initializeUuid;
    }

    public function __construct()
    {
        $this->initializeUuid();
        $this->materials = new ArrayCollection();
    }

    #[ORM\Column]
    public ?\DateTimeImmutable $startAt = null;

    #[ORM\Column]
    public ?\DateTimeImmutable $endAt = null;

    #[ORM\Column(enumType: LocationStatusEnum::class)]
    public ?LocationStatusEnum $status = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    public ?string $totalPrice = null;

    #[ORM\ManyToOne(targetEntity: Customer::class)]
    #[ORM\JoinColumn(referencedColumnName: 'uuid')]
    public ?Customer $customer = null;

    /**
     * @var Collection<int, Material>
     */
    #[ORM\ManyToMany(targetEntity: Material::class)]
    #[ORM\JoinTable(name: TableEnum::LOCATION_MATERIAL->value)]
    #[ORM\JoinColumn(name: 'location_uuid', referencedColumnName: 'uuid')]
    #[ORM\InverseJoinColumn(name: 'material_uuid', referencedColumnName: 'uuid')]
    public Collection $materials;

    public function addMaterial(Material $material): static
    {
        if (!$this->materials->contains($material)) {
            $this->materials->add($material);
        }

        return $this;
    }

    public function removeMaterial(Material $material): static
    {
        $this->materials->removeElement($material);

        return $this;
    }
}
