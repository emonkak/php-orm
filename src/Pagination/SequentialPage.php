<?php

namespace Emonkak\Orm\Pagination;

use Emonkak\Enumerable\EnumerableExtensions;
use Emonkak\Orm\Pagination\PaginatorInterface;

class SequentialPage implements \IteratorAggregate, PageInterface
{
    use EnumerableExtensions;

    /**
     * @var array
     */
    private $result;

    /**
     * @var PaginatorInterface
     */
    private $paginator;

    /**
     * @var int
     */
    private $index;

    /**
     * @param array              $result
     * @param int                $index
     * @param PaginatorInterface $paginator
     */
    public function __construct(array $result, $index, PaginatorInterface $paginator)
    {
        $this->result = $result;
        $this->paginator = $paginator;
        $this->index = $index;
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        $perPage = $this->paginator->getPerPage();
        return new \ArrayIterator(array_slice($this->result, 0, $perPage));
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
        if ($this->hasNext()) {
            return $this->paginator->at($this->index + 1);
        } else {
            return new SequentialPage([], $this->index + 1, $this->paginator);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function previous()
    {
        if ($this->hasPrevious()) {
            return $this->paginator->at($this->index - 1);
        } else {
            return new SequentialPage([], $this->index - 1, $this->paginator);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function hasPrevious()
    {
        return $this->index > 0;
    }

    /**
     * {@inheritDoc}
     */
    public function hasNext()
    {
        $perPage = $this->paginator->getPerPage();
        return count($this->result) > $perPage;
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
