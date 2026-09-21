<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Enum\LocationStatusEnum;
use App\Entity\Enum\MaterialStatusEnum;
use App\Entity\Location;
use App\Entity\Material;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Material>
 */
class MaterialRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Material::class);
    }

    public function findFiltered(bool $onlyCurrentlyRented): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('m')->orderBy('m.name', 'ASC');

        if ($onlyCurrentlyRented) {
            $now = new \DateTimeImmutable();
            $queryBuilder
                ->distinct()
                ->innerJoin(Location::class, 'l', Join::WITH, 'm MEMBER OF l.materials')
                ->andWhere('l.startAt <= :now')
                ->andWhere('l.endAt >= :now')
                ->andWhere('l.status NOT IN (:excludedStatuses)')
                ->setParameter('now', $now)
                ->setParameter('excludedStatuses', [LocationStatusEnum::DRAFT, LocationStatusEnum::CANCELLED]);
        }

        return $queryBuilder;
    }

    /**
     * Materials whose status field disagrees with whether they're actually
     * covered by an ongoing, non-cancelled location: marked available while
     * rented, or marked rented while nothing currently covers them.
     */
    public function findAnomalies(): QueryBuilder
    {
        $now = new \DateTimeImmutable();

        $currentlyRentedDql = static fn (string $alias): string => 'SELECT 1 FROM '.Location::class." {$alias}"
            ." WHERE m MEMBER OF {$alias}.materials"
            ." AND {$alias}.startAt <= :now AND {$alias}.endAt >= :now"
            ." AND {$alias}.status NOT IN (:excludedStatuses)";

        return $this->createQueryBuilder('m')
            ->andWhere(
                "(m.status = :available AND EXISTS ({$currentlyRentedDql('l1')}))"
                ." OR (m.status = :rented AND NOT EXISTS ({$currentlyRentedDql('l2')}))",
            )
            ->orderBy('m.name', 'ASC')
            ->setParameter('now', $now)
            ->setParameter('excludedStatuses', [LocationStatusEnum::DRAFT, LocationStatusEnum::CANCELLED])
            ->setParameter('available', MaterialStatusEnum::AVAILABLE)
            ->setParameter('rented', MaterialStatusEnum::RENTED);
    }

    public function countAnomalies(): int
    {
        return (int) (clone $this->findAnomalies())
            ->select('COUNT(m.uuid)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return array<string, int> Count of materials keyed by status value
     */
    public function countByStatus(): array
    {
        $rows = $this->createQueryBuilder('m')
            ->select('m.status AS status', 'COUNT(m.uuid) AS total')
            ->groupBy('m.status')
            ->getQuery()
            ->getArrayResult();

        $counts = [];
        foreach ($rows as $row) {
            $counts[$row['status']->value] = (int) $row['total'];
        }

        return $counts;
    }

    //    /**
    //     * @return Material[] Returns an array of Material objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('m.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Material
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
