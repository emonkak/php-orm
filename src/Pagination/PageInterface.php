<?php

declare(strict_types=1);

namespace Emonkak\Orm\Pagination;

use Emonkak\Enumerable\EnumerableInterface;

/**
 * @template T
 * @extends EnumerableInterface<T>
 */
interface PageInterface extends EnumerableInterface
{
    /**
     * @psalm-return PaginatorInterface<T>
     */
    public function getPaginator(): PaginatorInterface;

    public function getIndex(): int;

    public function getOffset(): int;

    /**
     * @psalm-return PageInterface<T>
     */
    public function previous(): PageInterface;

    /**
     * @psalm-return PageInterface<T>
     */
    public function next(): PageInterface;

    public function hasPrevious(): bool;

    public function hasNext(): bool;

    public function isFirst(): bool;

    public function isLast(): bool;
}
