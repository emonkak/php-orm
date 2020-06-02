<?php

declare(strict_types=1);

namespace Emonkak\Orm\Pagination;

/**
 * @template T
 * @extends AbstractPaginator<T>
 */
class PrecountPaginator extends AbstractPaginator
{
    /**
     * @psalm-var callable(int,int):\Traversable<T>
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
     * @psalm-param callable(int,int):\Traversable<T> $itemsFetcher
     * @psalm-param int $perPage
     * @psalm-param int $totalItems
     */
    public function __construct(callable $itemsFetcher, int $perPage, int $totalItems)
    {
        $this->itemsFetcher = $itemsFetcher;
        $this->perPage = $perPage;
        $this->totalItems = $totalItems;
    }

    /**
     * {@inheritdoc}
     */
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
