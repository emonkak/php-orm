<?php

namespace Emonkak\Orm\Pagination;

use Emonkak\Orm\ResultSet\PaginatedResultSet;
use Emonkak\Orm\ResultSet\ResultSetInterface;

interface PaginatorInterface extends ResultSetInterface
{
    /**
     * @param integer $index
     * @return PaginatedResultSet
     */
    public function at($index);

    /**
     * @return PaginatedResultSet
     */
    public function firstPage();

    /**
     * @return PaginatedResultSet
     */
    public function lastPage();

    /**
     * @return integer
     */
    public function getPerPage();

    /**
     * @return integer
     */
    public function getItemCount();

    /**
     * @return integer
     */
    public function getPageCount();
}
