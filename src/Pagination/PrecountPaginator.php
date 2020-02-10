<?php

declare(strict_types=1);

namespace Emonkak\Orm\Pagination;

class PrecountPaginator extends AbstractPaginator
{
    /**
     * @var callable(int,int):\Traversable
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
     * @param int $perPage
     * @param int $totalItems
     */
    public function __construct(callable $itemsFetcher, int $perPage, int $totalItems)
    {
        $this->itemsFetcher = $itemsFetcher;
        $this->perPage = $perPage;
        $this->totalItems = $totalItems;
    }

    public function at(int $index): PageInterface
    {
        if ($index >= 0 && $index < $this->getTotalPages()) {
            $itemsFetcher = $this->itemsFetcher;
            $items = $itemsFetcher($this->perPage * $index, $this->perPage);
        } else {
            $items = new \EmptyIterator();
        }

        return new Page($items, $index, $this);
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function getTotalItems(): int
    {
        return $this->totalItems;
    }
}
