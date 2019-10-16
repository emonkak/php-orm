<?php

namespace Emonkak\Orm\Pagination;

use Emonkak\Enumerable\EnumerableExtensions;

class CountablePage implements \IteratorAggregate, PageInterface
{
    use EnumerableExtensions;

    /**
     * @var \Traversable
     */
    private $result;

    /**
     * @var int
     */
    private $index;

    /**
     * @var CountablePaginatorInterface
     */
    private $paginator;

    /**
     * @param \Traversable                $result
     * @param int                         $index
     * @param CountablePaginatorInterface $paginator
     */
    public function __construct(\Traversable $result, $index, CountablePaginatorInterface $paginator)
    {
        $this->result = $result;
        $this->index = $index;
        $this->paginator = $paginator;
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return $this->result;
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
    public function getPaginator()
    {
        return $this->paginator;
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
    public function previous()
    {
        return $this->paginator->at($this->index - 1);
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
