<?php

namespace Emonkak\Orm\ResultSet;

use Emonkak\Orm\Paginator;
use Emonkak\Collection\Enumerable;
use Emonkak\Collection\EnumerableAliases;

/**
 * @internal
 */
class PaginatedResultSet implements ResultSetInterface
{
    use Enumerable;
    use EnumerableAliases;

    /**
     * @var ResultSetInterface
     */
    private $results;

    /**
     * @var Paginator
     */
    private $paginator;

    /**
     * @var integer
     */
    private $index;

    /**
     * @param ResultSetInterface $results
     * @param Paginator          $paginator
     * @param integer            $index
     */
    public function __construct(ResultSetInterface $results, Paginator $paginator, $index)
    {
        $this->results = $results;
        $this->paginator = $paginator;
        $this->index = $index;
    }

    /**
     * @return integer
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * @return integer
     */
    public function getPageNum()
    {
        return $this->index + 1;
    }

    /**
     * @return integer
     */
    public function getNumItems()
    {
        return $this->paginator->getNumItems();
    }

    /**
     * @return integer
     */
    public function getNumPages()
    {
        return $this->paginator->getNumPages();
    }

    /**
     * @return self
     */
    public function getNextPage()
    {
        if (!$this->hasNextPage()) {
            throw new \OutOfRangeException('The next page does not exist');
        }
        return $this->paginator->atIndex($this->index + 1);
    }

    /**
     * @return self
     */
    public function getPrevPage()
    {
        if (!$this->hasPrevPage()) {
            throw new \OutOfRangeException('The previous page does not exist');
        }
        return $this->paginator->atIndex($this->index - 1);
    }

    /**
     * @return boolean
     */
    public function hasPrevPage()
    {
        return $this->index > 0;
    }

    /**
     * @return boolean
     */
    public function hasNextPage()
    {
        return ($this->index + 1) < $this->paginator->getNumPages();
    }

    /**
     * @return boolean
     */
    public function isFirstPage()
    {
        return $this->index == 0;
    }

    /**
     * @return boolean
     */
    public function isLastPage()
    {
        return $this->index == ($this->paginator->getNumPages() - 1);
    }

    /**
     * {@inheritDoc}
     */
    public function count()
    {
        return $this->results->count();
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return $this->results->getIterator();
    }

    /**
     * {@inheritDoc}
     */
    public function getSource()
    {
        return $this->results;
    }

    /**
     * {@inheritDoc}
     */
    public function first()
    {
        return $this->results->first();
    }

    /**
     * {@inheritDoc}
     */
    public function all()
    {
        return $this->results->all();
    }
}
