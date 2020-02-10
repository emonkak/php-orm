<?php

declare(strict_types=1);

namespace Emonkak\Orm\Pagination;

use Emonkak\Enumerable\EnumerableInterface;

interface PaginatorInterface extends EnumerableInterface
{
    public function at(int $index): PageInterface;

    public function getPerPage(): int;

    public function has(int $index): bool;

    public function firstPage(): PageInterface;

    public function lastPage(): PageInterface;

    public function getTotalItems(): int;

    public function getTotalPages(): int;
}
