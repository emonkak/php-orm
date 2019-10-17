<?php

namespace Emonkak\Orm\Pagination;

use Emonkak\Enumerable\EnumerableInterface;

/**
 * @template T
 */
interface PageIteratorInterface extends EnumerableInterface
{
    /**
     * @return int
     */
    public function getPerPage();

    /**
     * @return self<T>
     */
    public function next();

    /**
     * @return bool
     */
    public function hasNext();

    /**
     * @return EnumerableInterface
     */
    public function iterate();
}
