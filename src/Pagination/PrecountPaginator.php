<?php

namespace Emonkak\Orm\Pagination;

class PrecountPaginator extends AbstractPaginator
{
    /**
     * @var callable
     */
    private $itemsFetcher;

    /**
     * @var int
     */
    private $perPage;

    /**
     * @var int
     */
    private $numItems;

    /**
     * @param callable(int,int):\Traversable $itemsFetcher
     * @param int                            $perPage
     * @param int                            $numItems
     */
    public function __construct(callable $itemsFetcher, $perPage, $numItems)
    {
        $this->itemsFetcher = $itemsFetcher;
        $this->perPage = $perPage;
        $this->numItems = $numItems;
    }

    /**
     * {@inheritDoc}
     */
    public function at($index)
    {
        if ($index >= 0 && $index < $this->getNumPages()) {
            $itemsFetcher = $this->itemsFetcher;
            $result = $itemsFetcher($this->perPage * $index, $this->perPage);
        } else {
            $result = new \EmptyIterator();
        }

        return new Page($result, $index, $this);
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
        return $this->numItems;
    }
}
