<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Enum\HistoryTypeEnum;
use App\Entity\History;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<History>
 */
class HistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, History::class);
    }

    public function findFiltered(?HistoryTypeEnum $type): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('h')->orderBy('h.createdAt', 'DESC');

        if (null !== $type) {
            $queryBuilder->andWhere('h.type = :type')->setParameter('type', $type);
        }

        return $queryBuilder;
    }
}
