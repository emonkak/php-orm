<?php

namespace Emonkak\Orm\Pagination;

use Emonkak\Orm\ResultSet\PaginatedResultSet;

interface PaginatorInterface
{
    /**
     * @param integer $index
     * @return PaginatedResultSet
     */
    public function at($index);

    /**
     * @return integer
     */
    public function getNumPages();
}
