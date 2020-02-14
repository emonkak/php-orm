<?php

declare(strict_types=1);

namespace Emonkak\Orm\Pagination;

use Emonkak\Enumerable\EnumerableInterface;

/**
 * @template T
 * @extends EnumerableInterface<T>
 */
interface PaginatorInterface extends EnumerableInterface
{
    /**
     * @psalm-return PageInterface<T>
     */
    public function at(int $index): PageInterface;

    public function getPerPage(): int;

    public function has(int $index): bool;

    /**
     * @psalm-return PageInterface<T>
     */
    public function firstPage(): PageInterface;

    /**
     * @psalm-return PageInterface<T>
     */
    public function lastPage(): PageInterface;

    public function getTotalItems(): int;

    public function getTotalPages(): int;
}
