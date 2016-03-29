<?php

namespace Emonkak\Orm\ResultSet;

use Emonkak\Collection\Enumerable;
use Emonkak\Collection\EnumerableAliases;

/**
 * @internal
 */
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
     * {@inheritDoc}
     */
    public function first()
    {
        foreach ($this->iterator as $result) {
            return $result;
        }
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function all()
    {
        return iterator_to_array($this->iterator, false);
    }
}
