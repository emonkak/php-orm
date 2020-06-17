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
     * {@inheritdoc}
     */
    public function getIterator(): \Traversable
    {
        return $this->items;
    }

    /**
     * {@inheritdoc}
     */
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

    /**
     * {@inheritdoc}
     */
    public function next(): PageInterface
    {
        return $this->paginator->at($this->index + 1);
    }

    /**
     * {@inheritdoc}
     */
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
