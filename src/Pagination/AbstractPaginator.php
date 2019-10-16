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
        for ($i = 0, $l = $this->getNumPages(); $i < $l; $i++) {
            foreach ($this->at($i) as $element) {
                yield $element;
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function has($index)
    {
        return 0 <= $index && $index < $this->getNumPages();
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
        $numPages = $this->getNumPages();
        return $this->at($numPages > 0 ? $numPages - 1 : 0);
    }

    /**
     * {@inheritDoc}
     */
    public function getNumPages()
    {
        return (int) ceil($this->getNumItems() / $this->getPerPage());
    }
}
