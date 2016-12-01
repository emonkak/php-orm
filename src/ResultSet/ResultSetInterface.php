<?php

namespace Emonkak\Orm\ResultSet;

use Emonkak\Enumerable\EnumerableInterface;

interface ResultSetInterface extends EnumerableInterface
{
    /**
     * @return string
     */
    public function getClass();
}
