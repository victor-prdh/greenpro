<?php

declare(strict_types=1);

namespace App\Tests\Pagination;

use App\Entity\Material;
use App\Pagination\Paginator;
use App\Repository\MaterialRepository;
use App\Tests\Fixtures\PaginatorFixtures;
use App\Tests\Support\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

class PaginatorTest extends KernelTestCase
{
    use FixturesTrait;

    private MaterialRepository $materialRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->loadFixtures([PaginatorFixtures::class]);
        $this->materialRepository = static::getContainer()->get(MaterialRepository::class);
    }

    public function testPaginateDefaultsToLimitOfThirtyAndFirstPage(): void
    {
        $paginator = new Paginator();
        $queryBuilder = $this->materialRepository->createQueryBuilder('m')->orderBy('m.reference', 'ASC');

        $pagination = $paginator->paginate($queryBuilder, Request::create('/'));

        self::assertSame(1, $pagination->page);
        self::assertSame(30, $pagination->limit);
        self::assertSame(7, $pagination->totalItems);
        self::assertSame(1, $pagination->totalPages);
        self::assertCount(7, $pagination->items);
        self::assertFalse($pagination->hasPrevious());
        self::assertFalse($pagination->hasNext());
    }

    public function testPaginateAppliesCustomLimitAndReturnsRequestedPage(): void
    {
        $paginator = new Paginator();
        $queryBuilder = $this->materialRepository->createQueryBuilder('m')->orderBy('m.reference', 'ASC');

        $pagination = $paginator->paginate($queryBuilder, Request::create('/', 'GET', ['page' => 2]), limit: 3);

        self::assertSame(2, $pagination->page);
        self::assertSame(3, $pagination->totalPages);
        self::assertSame(7, $pagination->totalItems);
        self::assertTrue($pagination->hasPrevious());
        self::assertTrue($pagination->hasNext());

        $references = array_map(static fn (Material $material): string => $material->reference, iterator_to_array($pagination));
        self::assertSame(['MAT-PAGE-004', 'MAT-PAGE-005', 'MAT-PAGE-006'], $references);
    }

    public function testPaginateClampsPageBelowOneToFirstPage(): void
    {
        $paginator = new Paginator();
        $queryBuilder = $this->materialRepository->createQueryBuilder('m')->orderBy('m.reference', 'ASC');

        $pagination = $paginator->paginate($queryBuilder, Request::create('/', 'GET', ['page' => 0]), limit: 3);

        self::assertSame(1, $pagination->page);
        self::assertFalse($pagination->hasPrevious());
    }

    public function testPaginateClampsPageAboveTotalPagesToLastPage(): void
    {
        $paginator = new Paginator();
        $queryBuilder = $this->materialRepository->createQueryBuilder('m')->orderBy('m.reference', 'ASC');

        $pagination = $paginator->paginate($queryBuilder, Request::create('/', 'GET', ['page' => 999]), limit: 3);

        self::assertSame(3, $pagination->page);
        self::assertCount(1, $pagination->items);
        self::assertFalse($pagination->hasNext());
    }

    public function testPaginateReturnsEmptyResultWhenNoRowsMatch(): void
    {
        $paginator = new Paginator();
        $queryBuilder = $this->materialRepository->createQueryBuilder('m')
            ->andWhere('m.reference = :reference')
            ->setParameter('reference', 'DOES-NOT-EXIST');

        $pagination = $paginator->paginate($queryBuilder, Request::create('/'));

        self::assertSame(1, $pagination->page);
        self::assertSame(0, $pagination->totalItems);
        self::assertSame(1, $pagination->totalPages);
        self::assertCount(0, $pagination->items);
    }
}
