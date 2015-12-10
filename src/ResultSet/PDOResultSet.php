<?php

namespace Emonkak\Orm\ResultSet;

use Emonkak\Collection\Enumerable;
use Emonkak\Collection\EnumerableAliases;
use Emonkak\Database\PDOStatementInterface;

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
        return $this->stmt->fetch();
    }

    /**
     * {@inheritDoc}
     */
    public function column($columnNumber = 0)
    {
        return $this->stmt->fetchAll(\PDO::FETCH_COLUMN, $columnNumber);
    }

    /**
     * {@inheritDoc}
     */
    public function value($columnNumber = 0)
    {
        return $this->stmt->fetchColumn($columnNumber);
    }
}
