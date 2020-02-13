<?php

declare(strict_types=1);

namespace Emonkak\Orm\Pagination;

use Emonkak\Enumerable\EnumerableInterface;

/**
 * @template T
 * @extends EnumerableInterface<T>
 */
interface PageIteratorInterface extends EnumerableInterface
{
    public function getPerPage(): int;

    /**
     * @psalm-return PageIteratorInterface<T>
     */
    public function next(): PageIteratorInterface;

    public function hasNext(): bool;

    /**
     * @psalm-return EnumerableInterface<T>
     */
    public function iterate(): EnumerableInterface;
}
