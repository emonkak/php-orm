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
        $this->stmt->execute();
        return $this->stmt;
    }

    /**
     * {@inheritDoc}
     */
    public function count()
    {
        $this->stmt->execute();
        return $this->stmt->rowCount();
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        $this->stmt->execute();
        return $this->stmt;
    }

    /**
     * {@inheritDoc}
     */
    public function all()
    {
        $this->stmt->execute();
        return $this->stmt->fetchAll();
    }

    /**
     * {@inheritDoc}
     */
    public function first()
    {
        $this->stmt->execute();
        $value = $this->stmt->fetch();
        return $value !== false ? $value : null;
    }
}
