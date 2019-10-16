<?php

namespace Emonkak\Orm\Pagination;

use Emonkak\Enumerable\EnumerableExtensions;

class EmptyPaginator extends \EmptyIterator implements CountablePaginatorInterface
{
    use EnumerableExtensions;

    /**
     * @var ?class-string
     */
    private $class;

    /**
     * @var int
     */
    private $perPage;

    /**
     * @param class-string $class
     * @param int          $perPage
     */
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
        return new CountablePage(new \EmptyIterator(), $index, $this);
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
