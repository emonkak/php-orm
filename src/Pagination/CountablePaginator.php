<?php

namespace Emonkak\Orm\Pagination;

class CountablePaginator extends AbstractCountablePaginator
{
    /**
     * @var callable
     */
    private $resultFetcher;

    /**
     * @var int
     */
    private $perPage;

    /**
     * @var int
     */
    private $numItems;

    /**
     * @param callable(int,int):\Traversable $resultFetcher
     * @param int                            $perPage
     * @param int                            $numItems
     */
    public function __construct(callable $resultFetcher, $perPage, $numItems)
    {
        $this->resultFetcher = $resultFetcher;
        $this->perPage = $perPage;
        $this->numItems = $numItems;
    }

    /**
     * {@inheritDoc}
     */
    public function at($index)
    {
        if ($index >= 0 && $index < $this->getNumPages()) {
            $resultFetcher = $this->resultFetcher;
            $result = $resultFetcher($this->perPage, $this->perPage * $index);
        } else {
            $result = new \EmptyIterator();
        }

        return new CountablePage($result, $index, $this);
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
