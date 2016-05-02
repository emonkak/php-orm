<?php

namespace Emonkak\Orm;

use Emonkak\Database\PDOInterface;
use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\ResultSet\PDOResultSet;
use Emonkak\Orm\ResultSet\ResultSetInterface;

trait Executable
{
    /**
     * @param PDOInterface $connection
     * @return PDOStatementInterface
     */
    public function execute(PDOInterface $connection)
    {
        $stmt = $this->prepare($connection);
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
        $stmt = $this->prepare($connection);
        return new PDOResultSet($stmt, $class);
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
