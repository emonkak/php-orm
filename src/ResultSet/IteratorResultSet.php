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
    public function count()
    {
        return iterator_count($this->iterator);
    }

    /**
     * @return mixed|null
     */
    public function first()
    {
        foreach ($this->iterator as $result) {
            return $result;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function all()
    {
        return iterator_to_array($this->iterator, false);
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

    /**
     * {@inheritDoc}
     */
    public function value($columnNumber = 0)
    {
        foreach ($this->iterator as $result) {
            $values = array_values((array) $result);
            return isset($values[$columnNumber]) ? $values[$columnNumber] : null;
        }

        return null;
    }
}
