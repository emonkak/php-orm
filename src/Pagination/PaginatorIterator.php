<?php

namespace Emonkak\Orm\Pagination;

class PaginatorIterator implements \IteratorAggregate
{
    /**
     * @var Paginator
     */
    private $paginator;

    /**
     * @param Paginator $paginator
     */
    public function __construct(Paginator $paginator)
    {
        $this->paginator = $paginator;
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        for ($i = 0, $l = $this->paginator->getPageCount(); $i < $l; $i++) {
            foreach ($this->paginator->at($i) as $element) {
                yield $element;
            }
        }
    }
}
