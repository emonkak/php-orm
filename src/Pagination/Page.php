<?php

declare(strict_types=1);

namespace Emonkak\Orm\Pagination;

/**
 * @template T
 * @extends AbstractPage<T>
 * @implements PaginatablePageInterface<T>
 */
class Page extends AbstractPage implements PaginatablePageInterface
{
    /**
     * @var \Traversable<T>
     */
    private \Traversable $items;

    private int $index;

    /**
     * @var PaginatorInterface<T>
     */
    private PaginatorInterface $paginator;

    /**
     * @param \Traversable<T> $items
     * @param PaginatorInterface<T> $paginator
     */
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

    public function getPerPage(): int
    {
        return $this->paginator->getPerPage();
    }

    public function getIndex(): int
    {
        return $this->index;
    }

    public function next(): PageInterface
    {
        return $this->paginator->at($this->index + 1);
    }

    public function previous(): PageInterface
    {
        return $this->paginator->at($this->index - 1);
    }

    public function hasNext(): bool
    {
        return $this->paginator->has($this->index + 1);
    }

    public function hasPrevious(): bool
    {
        return $this->paginator->has($this->index - 1);
    }
}
