<?php

declare(strict_types=1);

namespace Emonkak\Orm\Pagination;

use Emonkak\Enumerable\EnumerableInterface;

/**
 * @template T
 * @extends EnumerableInterface<T>
 * @extends \IteratorAggregate<T>
 */
interface PageInterface extends \IteratorAggregate, EnumerableInterface
{
    public function getPerPage(): int;

    public function getIndex(): int;

    public function getOffset(): int;

    /**
     * @psalm-return self<T>
     */
    public function next(): self;

    /**
     * @psalm-return self<T>
     */
    public function previous(): self;

    /**
     * @psalm-return iterable<self<T>>
     */
    public function forward(): iterable;

    /**
     * @psalm-return iterable<self<T>>
     */
    public function backward(): iterable;

    public function hasPrevious(): bool;

    public function hasNext(): bool;

    public function isFirst(): bool;

    public function isLast(): bool;
}
