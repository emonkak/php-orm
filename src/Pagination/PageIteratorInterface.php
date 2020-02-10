<?php

declare(strict_types=1);

namespace Emonkak\Orm\Pagination;

use Emonkak\Enumerable\EnumerableInterface;

interface PageIteratorInterface extends EnumerableInterface
{
    public function getPerPage(): int;

    public function next(): PageIteratorInterface;

    public function hasNext(): bool;

    public function iterate(): EnumerableInterface;
}
