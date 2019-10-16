<?php

namespace Emonkak\Orm\Pagination;

use Emonkak\Enumerable\EnumerableInterface;

interface PaginatorInterface extends EnumerableInterface
{
    /**
     * @param int $index
     * @return PageInterface
     */
    public function at($index);

    /**
     * @return int
     */
    public function getPerPage();
}
