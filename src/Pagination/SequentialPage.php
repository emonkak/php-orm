<?php

declare(strict_types=1);

namespace Emonkak\Orm\Pagination;

/**
 * @template T
 * @extends AbstractPage<T>
 */
class SequentialPage extends AbstractPage
{
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
    private $tailItems;

    /**
     * @template TStatic
     * @psalm-param callable(int,int):TStatic[] $itemsFetcher
     * @psalm-return self<TStatic>
     */
    public static function from(int $initialIndex, int $perPage, callable $itemsFetcher): self
    {
        $items = $itemsFetcher($initialIndex * $perPage, $perPage + 1);
        $tailItems = array_splice($items, $perPage);
        return new self($items, $tailItems, $initialIndex, $perPage, $itemsFetcher);
    }

    /**
     * @psalm-param T[] $items
     * @psalm-param T[] $tailItems
     * @psalm-param callable(int,int):T[] $itemsFetcher
     */
    private function __construct(array $items, array $tailItems, int $index, int $perPage, callable $itemsFetcher)
    {
        $this->items = $items;
        $this->tailItems = $tailItems;
        $this->index = $index;
        $this->perPage = $perPage;
        $this->itemsFetcher = $itemsFetcher;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->items);
    }

    /**
     * @psalm-return iterable<T>
     */
    public function getSource(): iterable
    {
        return $this->items;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function getIndex(): int
    {
        return $this->index;
    }

    /**
     * @psalm-return T[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    /**
     * {@inheritdoc}
     */
    public function next(): PageInterface
    {
        $index = $this->index + 1;
        if (count($this->tailItems) > 0) {
            $items = array_merge($this->tailItems, ($this->itemsFetcher)($index * $this->perPage + 1, $this->perPage));
            $tailItems = array_splice($items, $this->perPage);
        } else {
            $items = [];
            $tailItems = [];
        }
        return new self($items, $tailItems, $index, $this->perPage, $this->itemsFetcher);
    }

    /**
     * {@inheritdoc}
     */
    public function previous(): PageInterface
    {
        $index = $this->index - 1;
        if ($index >= 0) {
            $items = array_merge(($this->itemsFetcher)($index * $this->perPage, $this->perPage));
            $tailItems = array_slice($items, 0, 1);
        } else {
            $items = [];
            $tailItems = [];
        }
        return new self($items, $tailItems, $index, $this->perPage, $this->itemsFetcher);
    }

    public function hasNext(): bool
    {
        return $this->index < 0 || count($this->tailItems) > 0;
    }

    public function hasPrevious(): bool
    {
        return $this->index > 0;
    }
}
