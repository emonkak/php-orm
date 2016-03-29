<?php

namespace Emonkak\Orm\ResultSet;

use Emonkak\Collection\Enumerable;
use Emonkak\Collection\EnumerableAliases;
use Emonkak\Database\PDOStatementInterface;

/**
 * @internal
 */
class EmptyResultSet implements ResultSetInterface
{
    use Enumerable;
    use EnumerableAliases;

    /**
     * {@inheritDoc}
     */
    public function getSource()
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function count()
    {
        return 0;
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return new \EmptyIterator();
    }

    /**
     * {@inheritDoc}
     */
    public function all()
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function first()
    {
        return null;
    }
}
