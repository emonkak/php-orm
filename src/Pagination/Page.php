<?php

declare(strict_types=1);

namespace Emonkak\Orm\Pagination;

use Emonkak\Enumerable\EnumerableExtensions;

/**
 * @template T
 * @implements \IteratorAggregate<T>
 * @implements PageInterface<T>
 * @use EnumerableExtensions<T>
 */
class Page implements \IteratorAggregate, PageInterface
{
    use EnumerableExtensions;

    /**
     * @psalm-var \Traversable<T>
     * @var \Traversable
     */
    private $items;

    /**
     * @var int
     */
    private $index;

    /**
     * @psalm-var PaginatorInterface<T>
     * @var PaginatorInterface
     */
    private $paginator;

    /**
     * @psalm-param \Traversable<T> $items
     * @psalm-param PaginatorInterface<T> $paginator
     */
    public function __construct(\Traversable $items, int $index, PaginatorInterface $paginator)
    {
        $this->items = $items;
        $this->index = $index;
        $this->paginator = $paginator;
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator(): \Traversable
    {
        return $this->items;
    }

    /**
     * {@inheritDoc}
     */
    public function getPaginator(): PaginatorInterface
    {
        return $this->paginator;
    }

    public function getIndex(): int
    {
        return $this->index;
    }

    public function getOffset(): int
    {
        return $this->index * $this->paginator->getPerPage();
    }

    /**
     * {@inheritDoc}
     */
    public function previous(): PageInterface
    {
        return $this->paginator->at($this->index - 1);
    }

    /**
     * {@inheritDoc}
     */
    public function next(): PageInterface
    {
        return $this->paginator->at($this->index + 1);
    }

    public function hasPrevious(): bool
    {
        return $this->paginator->has($this->index - 1);
    }

    public function hasNext(): bool
    {
        return $this->paginator->has($this->index + 1);
    }

    public function isFirst(): bool
    {
        return !$this->hasPrevious();
    }

    public function isLast(): bool
    {
        return !$this->hasNext();
    }
}
