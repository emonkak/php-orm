<?php

declare(strict_types=1);

namespace Emonkak\Orm\Pagination;

use Emonkak\Enumerable\EnumerableExtensions;

class Page implements \IteratorAggregate, PageInterface
{
    use EnumerableExtensions;

    /**
     * @var \Traversable
     */
    private $items;

    /**
     * @var int
     */
    private $index;

    /**
     * @var PaginatorInterface
     */
    private $paginator;

    public function __construct(\Traversable $items, int $index, PaginatorInterface $paginator)
    {
        $this->items = $items;
        $this->index = $index;
        $this->paginator = $paginator;
    }

    public function getIterator(): \Traversable
    {
        return $this->items;
    }

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

    public function previous(): PageInterface
    {
        return $this->paginator->at($this->index - 1);
    }

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
