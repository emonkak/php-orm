<?php

namespace Emonkak\Orm\ResultSet;

interface ResultSetInterface extends \IteratorAggregate, \Countable
{
    /**
     * @return mixed
     */
    public function first();

    /**
     * @return mixed[]
     */
    public function all();
}
