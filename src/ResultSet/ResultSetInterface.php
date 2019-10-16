<?php

namespace Emonkak\Orm\ResultSet;

use Emonkak\Enumerable\EnumerableInterface;

interface ResultSetInterface extends EnumerableInterface
{
    /**
     * @return ?class-string
     */
    public function getClass();
}
