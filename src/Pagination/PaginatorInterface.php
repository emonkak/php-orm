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
     * @return PaginatablePageInterface<T>
     */
    public function at(int $index): PaginatablePageInterface;

    public function getPerPage(): int;

    public function has(int $index): bool;

    /**
     * @return PaginatablePageInterface<T>
     */
    public function firstPage(): PaginatablePageInterface;

    /**
     * @return PaginatablePageInterface<T>
     */
    public function lastPage(): PaginatablePageInterface;

    public function getTotalItems(): int;

    public function getTotalPages(): int;
}
