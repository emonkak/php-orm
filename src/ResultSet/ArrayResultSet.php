<?php

namespace Emonkak\Orm\ResultSet;

use Emonkak\Collection\Enumerable;
use Emonkak\Collection\EnumerableAliases;

class ArrayResultSet extends \ArrayObject implements ResultSetInterface
{
    use Enumerable;
    use EnumerableAliases;

    /**
     * {@inheritDoc}
     */
    public function getSource()
    {
        return $this->getArrayCopy();
    }

    /**
     * {@inheritDoc}
     */
    public function all()
    {
        return $this->getArrayCopy();
    }

    /**
     * {@inheritDoc}
     */
    public function column($columnNumber = 0)
    {
        $results = [];

        foreach ($this->getArrayCopy() as $result) {
            $values = array_values((array) $result);
            $results[] = isset($values[$columnNumber]) ? $values[$columnNumber] : null;
        }

        return $results;
    }
}
