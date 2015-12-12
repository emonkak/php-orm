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

    /**
     * @param integer $columnNumber
     * @return mixed[]
     */
    public function columns($columnNumber = 0);

    /**
     * @param integer $columnNumber
     * @return mixed
     */
    public function value($columnNumber = 0);
}
