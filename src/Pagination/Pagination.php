<?php

declare(strict_types=1);

namespace App\Pagination;

/**
 * @template T
 *
 * @implements \IteratorAggregate<int, T>
 */
final readonly class Pagination implements \IteratorAggregate, \Countable
{
    /**
     * @param list<T> $items
     */
    public function __construct(
        public array $items,
        public int $page,
        public int $limit,
        public int $totalItems,
        public int $totalPages,
    ) {
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->items);
    }

    public function count(): int
    {
        return \count($this->items);
    }

    public function hasPrevious(): bool
    {
        return $this->page > 1;
    }

    public function hasNext(): bool
    {
        return $this->page < $this->totalPages;
    }
}
