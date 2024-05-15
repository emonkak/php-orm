<?php

declare(strict_types=1);

namespace Emonkak\Orm\Pagination;

/**
 * @template T
 * @extends AbstractPaginator<T>
 */
class PrecountPaginator extends AbstractPaginator
{
    private int $perPage;

    private int $totalItems;

    /**
     * @var callable(int,int):\Traversable<T>
     */
    private $itemsFetcher;

    /**
     * @param callable(int,int):\Traversable<T> $itemsFetcher
     */
    public function __construct(int $perPage, int $totalItems, callable $itemsFetcher)
    {
        $this->perPage = $perPage;
        $this->totalItems = $totalItems;
        $this->itemsFetcher = $itemsFetcher;
    }

    public function at(int $index): PaginatablePageInterface
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
