<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Enum\LocationStatusEnum;
use App\Entity\Location;
use App\Entity\Material;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Location>
 */
class LocationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Location::class);
    }

    public function findFiltered(?LocationStatusEnum $status, bool $onlyCurrent): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('l')->orderBy('l.startAt', 'ASC');

        if (null !== $status) {
            $queryBuilder->andWhere('l.status = :status')->setParameter('status', $status);
        }

        if ($onlyCurrent) {
            $now = new \DateTimeImmutable();
            $queryBuilder->andWhere('l.startAt <= :now')
                ->andWhere('l.endAt >= :now')
                ->andWhere('l.status NOT IN (:excludedStatuses)')
                ->setParameter('now', $now)
                ->setParameter('excludedStatuses', [LocationStatusEnum::DRAFT, LocationStatusEnum::CANCELLED]);
        }

        return $queryBuilder;
    }

    /**
     * @return Location[]
     */
    public function findByMaterial(Material $material): array
    {
        return $this->createQueryBuilder('l')
            ->innerJoin('l.materials', 'm')
            ->andWhere('m.uuid = :materialUuid')
            ->orderBy('l.startAt', 'DESC')
            ->setParameter('materialUuid', $material->uuid, 'uuid')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<string, int> Count of locations keyed by status value
     */
    public function countByStatus(): array
    {
        $rows = $this->createQueryBuilder('l')
            ->select('l.status AS status', 'COUNT(l.uuid) AS total')
            ->groupBy('l.status')
            ->getQuery()
            ->getArrayResult();

        $counts = [];
        foreach ($rows as $row) {
            $counts[$row['status']->value] = (int) $row['total'];
        }

        return $counts;
    }

    public function findAnomalies(): QueryBuilder
    {
        $now = new \DateTimeImmutable();

        return $this->createQueryBuilder('l')
            ->andWhere('l.status != :cancelled')
            ->andWhere(
                '(l.startAt <= :now AND l.endAt >= :now AND l.status != :inProgress)'
                .' OR (l.endAt < :now AND l.status != :finished)',
            )
            ->orderBy('l.endAt', 'ASC')
            ->setParameter('now', $now)
            ->setParameter('cancelled', LocationStatusEnum::CANCELLED)
            ->setParameter('inProgress', LocationStatusEnum::IN_PROGRESS)
            ->setParameter('finished', LocationStatusEnum::FINISHED);
    }

    public function countAnomalies(): int
    {
        return (int) (clone $this->findAnomalies())
            ->select('COUNT(l.uuid)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countByStatusValue(LocationStatusEnum $status): int
    {
        return (int) $this->createQueryBuilder('l')
            ->select('COUNT(l.uuid)')
            ->andWhere('l.status = :status')
            ->setParameter('status', $status)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function sumRevenue(): string
    {
        $result = $this->createQueryBuilder('l')
            ->select('SUM(l.totalPrice)')
            ->andWhere('l.status != :cancelled')
            ->setParameter('cancelled', LocationStatusEnum::CANCELLED)
            ->getQuery()
            ->getSingleScalarResult();

        return $result ?? '0.00';
    }

    /**
     * @return list<array{createdAt: \DateTime, totalPrice: string, status: LocationStatusEnum}>
     */
    public function findActivitySince(\DateTimeImmutable $from): array
    {
        return $this->createQueryBuilder('l')
            ->select('l.createdAt AS createdAt', 'l.totalPrice AS totalPrice', 'l.status AS status')
            ->andWhere('l.createdAt >= :from')
            ->setParameter('from', $from)
            ->getQuery()
            ->getArrayResult();
    }

    //    /**
    //     * @return Location[] Returns an array of Location objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('l.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Location
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
