<?php

namespace Emonkak\Orm\Pagination;

use Emonkak\Enumerable\EnumerableExtensions;

abstract class AbstractPaginator implements \IteratorAggregate, PaginatorInterface
{
    use EnumerableExtensions;

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        for ($i = 0, $l = $this->getTotalPages(); $i < $l; $i++) {
            foreach ($this->at($i) as $item) {
                yield $item;
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function has($index)
    {
        return 0 <= $index && $index < $this->getTotalPages();
    }

    /**
     * {@inheritDoc}
     */
    public function firstPage()
    {
        return $this->at(0);
    }

    /**
     * {@inheritDoc}
     */
    public function lastPage()
    {
        $totalPages = $this->getTotalPages();
        return $this->at($totalPages > 0 ? $totalPages - 1 : 0);
    }

    /**
     * {@inheritDoc}
     */
    public function getTotalPages()
    {
        return (int) ceil($this->getTotalItems() / $this->getPerPage());
    }
}
