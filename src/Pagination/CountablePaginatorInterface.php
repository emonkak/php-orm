<?php

namespace Emonkak\Orm\Pagination;

interface CountablePaginatorInterface extends PaginatorInterface
{
    /**
     * @param int $index
     * @return bool
     */
    public function has($index);

    /**
     * @return CountablePage
     */
    public function firstPage();

    /**
     * @return CountablePage
     */
    public function lastPage();

    /**
     * @return int
     */
    public function getNumItems();

    /**
     * @return int
     */
    public function getNumPages();
}
