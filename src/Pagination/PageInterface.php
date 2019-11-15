<?php

namespace Emonkak\Orm\Pagination;

use Emonkak\Enumerable\EnumerableInterface;

interface PageInterface extends EnumerableInterface
{
    /**
     * @return int
     */
    public function getIndex();

    /**
     * @return int
     */
    public function getOffset();

    /**
     * @return PageInterface
     */
    public function previous();

    /**
     * @return PageInterface
     */
    public function next();

    /**
     * @return bool
     */
    public function hasPrevious();

    /**
     * @return bool
     */
    public function hasNext();

    /**
     * @return bool
     */
    public function isFirst();

    /**
     * @return bool
     */
    public function isLast();

    /**
     * @return self
     */
    public function freeze();
}
