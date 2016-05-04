<?php

namespace Emonkak\Orm\ResultSet;

use Emonkak\Orm\Paginator;
use Emonkak\Enumerable\EnumerableExtensions;

/**
 * @internal
 */
class PaginatedResultSet implements \IteratorAggregate, ResultSetInterface
{
    use EnumerableExtensions;

    /**
     * @var ResultSetInterface
     */
    private $result;

    /**
     * @var Paginator
     */
    private $paginator;

    /**
     * @var integer
     */
    private $index;

    /**
     * @param ResultSetInterface $result
     * @param Paginator          $paginator
     * @param integer            $index
     */
    public function __construct(ResultSetInterface $result, Paginator $paginator, $index)
    {
        $this->result = $result;
        $this->paginator = $paginator;
        $this->index = $index;
    }

    /**
     * {@inheritDoc}
     */
    public function getClass()
    {
        return $this->result->getClass();
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return $this->result;
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
     * @return PaginatedResultSet
     */
    public function nextPage()
    {
        if (!$this->hasNextPage()) {
            throw new \OutOfRangeException('The next page does not exist.');
        }
        return $this->paginator->atIndex($this->index + 1);
    }

    /**
     * @return PaginatedResultSet
     */
    public function prevPage()
    {
        if (!$this->hasPrevPage()) {
            throw new \OutOfRangeException('The previous page does not exist.');
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
}
