<?php

namespace Emonkak\Orm;

use Emonkak\Database\PDOInterface;
use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\ResultSet\PDOResultSet;

trait Executable
{
    /**
     * @param PDOInterface $connection
     * @return PDOStatementInterface
     */
    public function execute(PDOInterface $connection)
    {
        list ($sql, $binds) = $this->build();

        $stmt = $connection->prepare($sql);

        $this->bindTo($stmt, $binds);

        $stmt->execute();

        return $stmt;
    }

    /**
     * @param PDOInterface $connection
     * @param string       $class
     * @return ResultSetInterface
     */
    public function getResult(PDOInterface $connection, $class)
    {
        list ($sql, $binds) = $this->build();

        $stmt = $connection->prepare($sql);

        $this->bindTo($stmt, $binds);

        $stmt->setFetchMode(\PDO::FETCH_CLASS, $class);

        return new PDOResultSet($stmt);
    }

    /**
     * @param PDOStatementInterface $stmt
     * @param mixed[]               $binds
     */
    private function bindTo(PDOStatementInterface $stmt, array $binds)
    {
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
    }

    /**
     * @return array (sql: string, binds: mixed)
     */
    abstract public function build();
}
