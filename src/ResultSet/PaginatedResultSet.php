<?php

namespace Emonkak\Orm\ResultSet;

use Emonkak\Enumerable\EnumerableInterface;
use Emonkak\Enumerable\EnumerableExtensions;
use Emonkak\Orm\Pagination\PaginatorInterface;

class PaginatedResultSet implements \IteratorAggregate, ResultSetInterface
{
    use EnumerableExtensions;

    /**
     * @var EnumerableInterface
     */
    private $elements;

    /**
     * @var PaginatorInterface
     */
    private $paginator;

    /**
     * @var int
     */
    private $index;

    /**
     * @param EnumerableInterface $elements
     * @param PaginatorInterface  $paginator
     * @param int                 $index
     */
    public function __construct(EnumerableInterface $elements, PaginatorInterface $paginator, $index)
    {
        $this->elements = $elements;
        $this->paginator = $paginator;
        $this->index = $index;
    }

    /**
     * {@inheritDoc}
     */
    public function getClass()
    {
        return $this->paginator->getClass();
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return $this->elements;
    }

    /**
     * @return int
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * @return int
     */
    public function getOffset()
    {
        return $this->index * $this->paginator->getPerPage();
    }

    /**
     * @return PaginatorInterface
     */
    public function getPaginator()
    {
        return $this->paginator;
    }

    /**
     * @return PaginatedResultSet
     */
    public function nextPage()
    {
        return $this->paginator->at($this->index + 1);
    }

    /**
     * @return PaginatedResultSet
     */
    public function prevPage()
    {
        return $this->paginator->at($this->index - 1);
    }

    /**
     * @return bool
     */
    public function hasPrevPage()
    {
        return $this->paginator->has($this->index - 1);
    }

    /**
     * @return bool
     */
    public function hasNextPage()
    {
        return $this->paginator->has($this->index + 1);
    }

    /**
     * @return bool
     */
    public function isFirstPage()
    {
        return !$this->hasPrevPage();
    }

    /**
     * @return bool
     */
    public function isLastPage()
    {
        return !$this->hasNextPage();
    }
}
