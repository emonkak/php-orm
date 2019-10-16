<?php

namespace Emonkak\Orm\Pagination;

use Emonkak\Enumerable\Enumerable;
use Emonkak\Enumerable\EnumerableExtensions;
use Emonkak\Orm\ResultSet\PaginatedResultSet;

class EmptyPaginator extends \EmptyIterator implements PaginatorInterface
{
    use EnumerableExtensions;

    /**
     * @var string
     */
    private $class;

    /**
     * @var int
     */
    private $perPage;

    public function __construct($class, $perPage)
    {
        $this->class = $class;
        $this->perPage = $perPage;
    }

    /**
     * {@inheritDoc}
     */
    public function at($index)
    {
        return new PaginatedResultSet(Enumerable::_empty(), $this, $index);
    }

    /**
     * {@inheritDoc}
     */
    public function has($index)
    {
        return false;
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
        return $this->at(0);
    }

    /**
     * {@inheritDoc}
     */
    public function getPerPage()
    {
        return $this->perPage;
    }

    /**
     * {@inheritDoc}
     */
    public function getNumItems()
    {
        return 0;
    }

    /**
     * {@inheritDoc}
     */
    public function getNumPages()
    {
        return 0;
    }

    /**
     * {@inheritDoc}
     */
    public function getClass()
    {
        return $this->class;
    }
}
