<?php

namespace Emonkak\Orm\ResultSet;

use Emonkak\Collection\Enumerable;
use Emonkak\Collection\EnumerableAliases;
use Emonkak\Database\PDOStatementInterface;

/**
 * @internal
 */
class PDOResultSet implements ResultSetInterface
{
    use Enumerable;
    use EnumerableAliases;

    /**
     * @var PDOStatementInterface
     */
    private $stmt;

    /**
     * @param PDOStatementInterface $stmt
     */
    public function __construct(PDOStatementInterface $stmt)
    {
        $this->stmt = $stmt;
    }

    /**
     * {@inheritDoc}
     */
    public function getSource()
    {
        return $this->stmt;
    }

    /**
     * {@inheritDoc}
     */
    public function count()
    {
        return $this->stmt->rowCount();
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return $this->stmt;
    }

    /**
     * {@inheritDoc}
     */
    public function all()
    {
        return $this->stmt->fetchAll();
    }

    /**
     * {@inheritDoc}
     */
    public function first()
    {
        $value = $this->stmt->fetch();
        return $value !== false ? $value : null;
    }
}
