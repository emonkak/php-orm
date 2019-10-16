<?php

namespace Emonkak\Orm\Pagination;

use Emonkak\Orm\ResultSet\PaginatedResultSet;
use Emonkak\Orm\ResultSet\ResultSetInterface;

interface PaginatorInterface extends ResultSetInterface
{
    /**
     * @param int $index
     * @return PaginatedResultSet
     */
    public function at($index);

    /**
     * @param int $index
     * @return bool
     */
    public function has($index);

    /**
     * @return PaginatedResultSet
     */
    public function firstPage();

    /**
     * @return PaginatedResultSet
     */
    public function lastPage();

    /**
     * @return int
     */
    public function getPerPage();

    /**
     * @return int
     */
    public function getNumItems();

    /**
     * @return int
     */
    public function getNumPages();
}
