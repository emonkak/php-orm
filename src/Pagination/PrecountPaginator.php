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
    private $totalItems;

    /**
     * @param callable(int,int):\Traversable $itemsFetcher
     * @param int                            $perPage
     * @param int                            $totalItems
     */
    public function __construct(callable $itemsFetcher, $perPage, $totalItems)
    {
        $this->itemsFetcher = $itemsFetcher;
        $this->perPage = $perPage;
        $this->totalItems = $totalItems;
    }

    /**
     * {@inheritDoc}
     */
    public function at($index)
    {
        if ($index >= 0 && $index < $this->getTotalPages()) {
            $itemsFetcher = $this->itemsFetcher;
            $items = $itemsFetcher($this->perPage * $index, $this->perPage);
        } else {
            $items = new \EmptyIterator();
        }

        return new Page($items, $index, $this);
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
    public function getTotalItems()
    {
        return $this->totalItems;
    }
}
