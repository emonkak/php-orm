<?php

declare(strict_types=1);

namespace Emonkak\Orm\Pagination;

use Emonkak\Enumerable\EnumerableInterface;

interface PageInterface extends EnumerableInterface
{
    public function getPaginator(): PaginatorInterface;

    public function getIndex(): int;

    public function getOffset(): int;

    public function previous(): PageInterface;

    public function next(): PageInterface;

    public function hasPrevious(): bool;

    public function hasNext(): bool;

    public function isFirst(): bool;

    public function isLast(): bool;
}
