<?php

namespace Emonkak\Orm\Pagination;

use Emonkak\Enumerable\Enumerable;
use Emonkak\Enumerable\EnumerableExtensions;

/**
 * @template T
 */
class SequentialPageIterator implements \IteratorAggregate, PageIteratorInterface
{
    use EnumerableExtensions;

    /**
     * @var int
     */
    private $index;

    /**
     * @var int
     */
    private $perPage;

    /**
     * @var callable(int,int):T[]
     */
    private $itemsFetcher;

    /**
     * @var T[]
     */
    private $items;

    /**
     * @var T[]
     */
    private $extraItems;

    /**
     * @template T
     * @param int                   $index
     * @param int                   $perPage
     * @param callable(int,int):T[] $itemsFetcher
     * @return SequentialPageIterator<T>
     */
    public static function from($index, $perPage, callable $itemsFetcher)
    {
        $items = $itemsFetcher($index * $perPage, $perPage + 1);
        $extraItems = array_splice($items, $perPage);
        return new self($index, $perPage, $itemsFetcher, $items, $extraItems);
    }

    /**
     * @param int                   $index
     * @param int                   $perPage
     * @param callable(int,int):T[] $itemsFetcher
     * @param T[]                   $items
     * @param T[]                   $extraItems
     */
    private function __construct($index, $perPage, callable $itemsFetcher, array $items, array $extraItems)
    {
        $this->index = $index;
        $this->perPage = $perPage;
        $this->itemsFetcher = $itemsFetcher;
        $this->items = $items;
        $this->extraItems = $extraItems;
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->items);
    }

    /**
     * @return int
     */
    public function getIndex()
    {
        return $this->index;
    }

    /**
     * {@inheritDoc}
     */
    public function getPerPage()
    {
        return $this->perPage;
    }

    /**
     * @return int
     */
    public function getOffset()
    {
        return $this->index * $this->perPage;
    }

    /**
     * @return T[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * {@inheritDoc}
     */
    public function next()
    {
        $nextIndex = $this->index + 1;
        $perPage = $this->perPage;
        $itemsFetcher = $this->itemsFetcher;

        $nextItems = array_merge($this->extraItems, $itemsFetcher($nextIndex * $perPage + 1, $perPage));
        $nextExtraItems = array_splice($nextItems, $perPage);

        return new self($nextIndex, $perPage, $itemsFetcher, $nextItems, $nextExtraItems);
    }

    /**
     * {@inheritDoc}
     */
    public function hasNext()
    {
        return count($this->extraItems) > 0;
    }

    /**
     * {@inheritDoc}
     */
    public function iterate()
    {
        return Enumerable::defer(function() {
            $page = $this;

            foreach ($page->items as $item) {
                yield $item;
            }

            while ($page->hasNext()) {
                $page = $page->next();

                foreach ($page->items as $item) {
                    yield $item;
                }
            }
        });
    }
}
