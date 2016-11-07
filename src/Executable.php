<?php

namespace Emonkak\Orm;

use Emonkak\Database\PDOInterface;
use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\ResultSet\PDOResultSet;
use Emonkak\Orm\ResultSet\ResultSetInterface;

trait Executable
{
    /**
     * @var callable[]
     */
    private $observers = [];

    /**
     * Whether this query is executing.
     *
     * @var boolean
     */
    private $executing = false;

    /**
     * @param callable $observer (query: QueryInterface, connection: PDOInterface) -> QueryInterface
     * @return self
     */
    public function observe(callable $observer)
    {
        $cloned = clone $this;
        $cloned->observers[] = $observer;
        return $cloned;
    }

    /**
     * @param PDOInterface $connection
     * @return PDOStatementInterface
     */
    public function execute(PDOInterface $connection)
    {
        if ($this->executing) {
            $stmt = $this->prepare($connection);
            $stmt->execute();
            return $stmt;
        } else {
            $this->executing = true;
            try {
                return $this->applyObservers($connection)->execute($connection);
            } finally {
                $this->executing = false;
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getResult(PDOInterface $connection, $class)
    {
        if ($this->executing) {
            $stmt = $this->prepare($connection);
            return new PDOResultSet($stmt, $class);
        } else {
            $this->executing = true;
            try {
                return $this->applyObservers($connection)->getResult($connection, $class);
            } finally {
                $this->executing = false;
            }
        }
    }

    /**
     * @param PDOInterface $connection
     * @return QueryInterface
     */
    private function applyObservers(PDOInterface $connection)
    {
        $query = $this;

        foreach ($this->observers as $observer) {
            $query = $observer($query, $connection);
        }

        return $query;
    }

    /**
     * @param PDOInterface $connection
     * @return PDOStatementInterface
     */
    private function prepare(PDOInterface $connection)
    {
        list ($sql, $binds) = $this->build();

        $stmt = $connection->prepare($sql);

        foreach ($binds as $index => $bind) {
            $type = gettype($bind);
            switch ($type) {
            case 'boolean':
                $stmt->bindValue($index + 1, $bind, \PDO::PARAM_BOOL);
                break;
            case 'integer':
                $stmt->bindValue($index + 1, $bind, \PDO::PARAM_INT);
                break;
            case 'double':
            case 'string':
                $stmt->bindValue($index + 1, $bind, \PDO::PARAM_STR);
                break;
            case 'NULL':
                $stmt->bindValue($index + 1, $bind, \PDO::PARAM_NULL);
                break;
            default:
                throw new \LogicException(sprintf('Invalid value, got "%s".', $type));
            }
        }

        return $stmt;
    }

    /**
     * @return array (sql: string, binds: mixed)
     */
    abstract public function build();
}
