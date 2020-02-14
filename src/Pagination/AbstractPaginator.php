<?php

declare(strict_types=1);

namespace Emonkak\Orm\Pagination;

use Emonkak\Enumerable\EnumerableExtensions;

/**
 * @template T
 * @implements \IteratorAggregate<T>
 * @implements PaginatorInterface<T>
 */
abstract class AbstractPaginator implements \IteratorAggregate, PaginatorInterface
{
    use EnumerableExtensions;

    /**
     * @psalm-return \Traversable<T>
     */
    public function getIterator(): \Traversable
    {
        for ($i = 0, $l = $this->getTotalPages(); $i < $l; $i++) {
            foreach ($this->at($i) as $item) {
                yield $item;
            }
        }
    }

    public function has(int $index): bool
    {
        return 0 <= $index && $index < $this->getTotalPages();
    }

    /**
     * {@inheritDoc}
     */
    public function firstPage(): PageInterface
    {
        return $this->at(0);
    }

    /**
     * {@inheritDoc}
     */
    public function lastPage(): PageInterface
    {
        $totalPages = $this->getTotalPages();
        return $this->at($totalPages > 0 ? $totalPages - 1 : 0);
    }

    public function getTotalPages(): int
    {
        return (int) ceil($this->getTotalItems() / $this->getPerPage());
    }
}
