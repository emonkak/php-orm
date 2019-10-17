<?php

namespace Emonkak\Orm\Pagination;

use Emonkak\Enumerable\EnumerableExtensions;

class SequentialPaginator implements \IteratorAggregate, PaginatorInterface
{
    use EnumerableExtensions;

    /**
     * @var callable(int,int):mixed[]
     */
    private $itemsFetcher;

    /**
     * @var int
     */
    private $perPage;

    /**
     * @param callable(int,int):mixed[] $itemsFetcher
     * @param int                       $perPage
     */
    public function __construct(callable $itemsFetcher, $perPage)
    {
        $this->itemsFetcher = $itemsFetcher;
        $this->perPage = $perPage;
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        $itemsFetcher = $this->itemsFetcher;
        $perPage = $this->perPage;

        $index = 0;
        $newItems = $itemsFetcher($perPage + 1, $index * $perPage);
        $extraItems = array_splice($newItems, $perPage);

        foreach ($newItems as $item) {
            yield $item;
        }

        while (count($extraItems) > 0) {
            foreach ($extraItems as $item) {
                yield $item;
            }

            $index++;
            $newItems = $itemsFetcher($perPage, $index * $perPage + 1);
            $extraItems = array_splice($newItems, $perPage - 1);

            foreach ($newItems as $item) {
                yield $item;
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function at($index)
    {
        $itemsFetcher = $this->itemsFetcher;
        $perPage = $this->perPage;
        $items = $itemsFetcher($perPage + 1, $index * $perPage);
        return new SequentialPage($items, $index, $this);
    }

    /**
     * {@inheritDoc}
     */
    public function getPerPage()
    {
        return $this->perPage;
    }
}
