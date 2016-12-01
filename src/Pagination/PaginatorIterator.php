<?php

namespace Emonkak\Orm\Pagination;

class PaginatorIterator implements \IteratorAggregate
{
    /**
     * @var PaginatorInterface
     */
    private $paginator;

    /**
     * @param PaginatorInterface $paginator
     */
    public function __construct(PaginatorInterface $paginator)
    {
        $this->paginator = $paginator;
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        for ($i = 0, $l = $this->paginator->getNumPages(); $i < $l; $i++) {
            yield $this->paginator->at($i);
        }
    }
}
