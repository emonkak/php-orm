<?php

namespace Emonkak\Orm\Pagination;

use Emonkak\Enumerable\EnumerableExtensions;
use Emonkak\Orm\ResultSet\EmptyResultSet;
use Emonkak\Orm\ResultSet\PaginatedResultSet;

class EmptyPaginator extends \EmptyIterator implements PaginatorInterface
{
    use EnumerableExtensions;

    /**
     * @var string
     */
    private $class;

    /**
     * @var integer
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
        if ($index < 0) {
            throw new \OutOfRangeException(
                "The value must be greater than or equal to zero. but got '$index'."
            );
        }

        return new PaginatedResultSet(new EmptyResultSet($this->class), $this, $index);
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
    public function getItemCount()
    {
        return 0;
    }

    /**
     * {@inheritDoc}
     */
    public function getPageCount()
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
