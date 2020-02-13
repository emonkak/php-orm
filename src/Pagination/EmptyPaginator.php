<?php

declare(strict_types=1);

namespace Emonkak\Orm\Pagination;

use Emonkak\Enumerable\EnumerableExtensions;

/**
 * @implements PaginatorInterface<mixed>
 */
class EmptyPaginator extends \EmptyIterator implements PaginatorInterface
{
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
     * @psalm-return PageInterface<mixed>
     */
    public function at(int $index): PageInterface
    {
        return new Page(new \EmptyIterator(), $index, $this);
    }

    public function has(int $index): bool
    {
        return false;
    }

    /**
     * @psalm-return PageInterface<mixed>
     */
    public function firstPage(): PageInterface
    {
        return $this->at(0);
    }

    /**
     * @psalm-return PageInterface<mixed>
     */
    public function lastPage(): PageInterface
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
