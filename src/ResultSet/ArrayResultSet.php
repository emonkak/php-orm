<?php

namespace Emonkak\Orm\ResultSet;

use Emonkak\Collection\Enumerable;
use Emonkak\Collection\EnumerableAliases;

/**
 * @internal
 */
class ArrayResultSet extends \ArrayObject implements ResultSetInterface
{
    use Enumerable;
    use EnumerableAliases;

    /**
     * {@inheritDoc}
     */
    public function getSource()
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function first()
    {
        foreach ($this as $result) {
            return $result;
        }
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
    public function columns($columnNumber = 0)
    {
        $results = [];

        foreach ($this as $result) {
            $values = array_values((array) $result);
            $results[] = isset($values[$columnNumber]) ? $values[$columnNumber] : null;
        }

        return $results;
    }

    /**
     * {@inheritDoc}
     */
    public function value($columnNumber = 0)
    {
        foreach ($this as $result) {
            $values = array_values((array) $result);
            return isset($values[$columnNumber]) ? $values[$columnNumber] : null;
        }

        return null;
    }
}
