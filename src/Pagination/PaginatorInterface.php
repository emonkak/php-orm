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

    /**
     * @param int $index
     * @return bool
     */
    public function has($index);

    /**
     * @return Page
     */
    public function firstPage();

    /**
     * @return Page
     */
    public function lastPage();

    /**
     * @return int
     */
    public function getTotalItems();

    /**
     * @return int
     */
    public function getTotalPages();
}
