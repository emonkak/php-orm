<?php

declare(strict_types=1);

namespace Emonkak\Orm\Pagination;

use Emonkak\Enumerable\EnumerableExtensions;

/**
 * @template T
 * @implements PaginatorInterface<T>
 */
class EmptyPaginator extends \EmptyIterator implements PaginatorInterface
{
    /**
     * @use EnumerableExtensions<T>
     */
    use EnumerableExtensions;

    /**
     * @var int
     */
    private $perPage;

    /**
     * @psalm-param int $perPage
     */
    public function __construct(int $perPage)
    {
        $this->perPage = $perPage;
    }

    /**
     * {@inheritdoc}
     */
    public function at(int $index): PaginatablePageInterface
    {
        return new Page(new \EmptyIterator(), $index, $this);
    }

    public function has(int $index): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function firstPage(): PaginatablePageInterface
    {
        return $this->at(0);
    }

    /**
     * {@inheritdoc}
     */
    public function lastPage(): PaginatablePageInterface
    {
        return $this->at(0);
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function getTotalItems(): int
    {
        return 0;
    }

    public function getTotalPages(): int
    {
        return 0;
    }
}
