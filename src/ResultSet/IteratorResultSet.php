<?php

namespace Emonkak\Orm\ResultSet;

use Emonkak\Collection\Enumerable;
use Emonkak\Collection\EnumerableAliases;

class IteratorResultSet implements ResultSetInterface
{
    use Enumerable;
    use EnumerableAliases;

    /**
     * @var \Iterator
     */
    private $iterator;

    /**
     * @param \Iterator
     */
    public function __construct(\Iterator $iterator)
    {
        $this->iterator = $iterator;
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return $this->iterator;
    }

    /**
     * {@inheritDoc}
     */
    public function getSource()
    {
        return $this->iterator;
    }

    /**
     * {@inheritDoc}
     */
    public function all()
    {
        return $this->toList();
    }

    /**
     * {@inheritDoc}
     */
    public function column($columnNumber = 0)
    {
        $results = [];

        foreach ($this->iterator as $result) {
            $values = array_values((array) $result);
            $results[] = isset($values[$columnNumber]) ? $values[$columnNumber] : null;
        }

        return $results;
    }
}
