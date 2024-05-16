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
     * @return self<T>
     */
    public function next(): self;

    /**
     * @return self<T>
     */
    public function previous(): self;

    /**
     * @return \Traversable<self<T>>
     */
    public function forward(): \Traversable;

    /**
     * @return \Traversable<self<T>>
     */
    public function backward(): \Traversable;

    public function hasPrevious(): bool;

    public function hasNext(): bool;

    public function isFirst(): bool;

    public function isLast(): bool;
}
