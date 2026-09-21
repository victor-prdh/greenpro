<?php

declare(strict_types=1);

namespace App\Pagination;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\OffsetPaginator;
use Doctrine\ORM\Tools\Pagination\Window;
use Symfony\Component\HttpFoundation\Request;

final class Paginator
{
    private readonly OffsetPaginator $paginator;

    public function __construct()
    {
        $this->paginator = new OffsetPaginator();
    }

    public function paginate(QueryBuilder $queryBuilder, Request $request, int $limit = 30): Pagination
    {
        $page = max($request->query->getInt('page', 1), 1);

        $windowPage = $this->paginator->paginate($queryBuilder, Window::fromPageNumberAndSize($page, $limit));

        if ($page > $windowPage->getPageCount()) {
            $page = $windowPage->getPageCount();
            $windowPage = $this->paginator->paginate($queryBuilder, Window::fromPageNumberAndSize($page, $limit));
        }

        return new Pagination($windowPage->getItems(), $page, $limit, $windowPage->getTotalCount(), $windowPage->getPageCount());
    }
}
