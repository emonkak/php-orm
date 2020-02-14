<?php

declare(strict_types=1);

namespace Emonkak\Orm\Pagination;

use Emonkak\Enumerable\Enumerable;
use Emonkak\Enumerable\EnumerableExtensions;
use Emonkak\Enumerable\EnumerableInterface;

/**
 * @template T
 * @implements \IteratorAggregate<T>
 * @implements PageIteratorInterface<T>
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
     * @psalm-var callable(int,int):T[]
     * @var callable
     */
    private $itemsFetcher;

    /**
     * @psalm-var T[]
     * @var mixed[]
     */
    private $items;

    /**
     * @psalm-var T[]
     * @var mixed[]
     */
    private $extraItems;

    /**
     * @template TStatic
     * @psalm-param int $index
     * @psalm-param int $perPage
     * @psalm-param callable(int,int):TStatic[] $itemsFetcher
     * @psalm-return self<TStatic>
     */
    public static function from(int $index, int $perPage, callable $itemsFetcher): self
    {
        $items = $itemsFetcher($index * $perPage, $perPage + 1);
        $extraItems = array_splice($items, $perPage);
        return new self($index, $perPage, $itemsFetcher, $items, $extraItems);
    }

    /**
     * @psalm-param int $index
     * @psalm-param int $perPage
     * @psalm-param callable(int,int):T[] $itemsFetcher
     * @psalm-param T[] $items
     * @psalm-param T[] $extraItems
     */
    private function __construct(int $index, int $perPage, callable $itemsFetcher, array $items, array $extraItems)
    {
        $this->index = $index;
        $this->perPage = $perPage;
        $this->itemsFetcher = $itemsFetcher;
        $this->items = $items;
        $this->extraItems = $extraItems;
    }

    /**
     * @psalm-return \Traversable<T>
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->items);
    }

    public function getIndex(): int
    {
        return $this->index;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function getOffset(): int
    {
        return $this->index * $this->perPage;
    }

    /**
     * @psalm-return T[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * {@inheritDoc}
     */
    public function next(): PageIteratorInterface
    {
        $nextIndex = $this->index + 1;
        $perPage = $this->perPage;
        $itemsFetcher = $this->itemsFetcher;

        $nextItems = array_merge($this->extraItems, $itemsFetcher($nextIndex * $perPage + 1, $perPage));
        $nextExtraItems = array_splice($nextItems, $perPage);

        return new self($nextIndex, $perPage, $itemsFetcher, $nextItems, $nextExtraItems);
    }

    public function hasNext(): bool
    {
        return count($this->extraItems) > 0;
    }

    /**
     * {@inheritDoc}
     */
    public function iterate(): EnumerableInterface
    {
        return Enumerable::defer(function(): iterable {
            $page = $this;

            foreach ($page->items as $item) {
                yield $item;
            }

            while ($page->hasNext()) {
                $page = $page->next();

                foreach ($page as $item) {
                    yield $item;
                }
            }
        });
    }
}
