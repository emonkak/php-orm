<?php

namespace Emonkak\Orm\Pagination;

use Emonkak\Enumerable\EnumerableExtensions;

class Page implements \IteratorAggregate, PageInterface
{
    use EnumerableExtensions;

    /**
     * @var \Traversable
     */
    private $items;

    /**
     * @var int
     */
    private $index;

    /**
     * @var PaginatorInterface
     */
    private $paginator;

    /**
     * @param \Traversable       $items
     * @param int                $index
     * @param PaginatorInterface $paginator
     */
    public function __construct(\Traversable $items, $index, PaginatorInterface $paginator)
    {
        $this->items = $items;
        $this->index = $index;
        $this->paginator = $paginator;
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return $this->items;
    }

    /**
     * {@inheritDoc}
     */
    public function getPaginator()
    {
        return $this->paginator;
    }

    /**
     * {@inheritDoc}
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * {@inheritDoc}
     */
    public function getOffset()
    {
        return $this->index * $this->paginator->getPerPage();
    }

    /**
     * {@inheritDoc}
     */
    public function previous()
    {
        return $this->paginator->at($this->index - 1);
    }

    /**
     * {@inheritDoc}
     */
    public function next()
    {
        return $this->paginator->at($this->index + 1);
    }

    /**
     * {@inheritDoc}
     */
    public function hasPrevious()
    {
        return $this->paginator->has($this->index - 1);
    }

    /**
     * {@inheritDoc}
     */
    public function hasNext()
    {
        return $this->paginator->has($this->index + 1);
    }

    /**
     * {@inheritDoc}
     */
    public function isFirst()
    {
        return !$this->hasPrevious();
    }

    /**
     * {@inheritDoc}
     */
    public function isLast()
    {
        return !$this->hasNext();
    }
}
