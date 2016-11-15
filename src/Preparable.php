<?php

namespace Emonkak\Orm;

use Emonkak\Database\PDOInterface;
use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\Sql;

/**
 * @internal
 */
trait Preparable
{
    /**
     * @param PDOInterface $connection
     * @return PDOStatementInterface
     */
    public function prepare(PDOInterface $connection)
    {
        $query = $this->build();

        $stmt = $connection->prepare($query->getSql());

        foreach ($query->getBindings() as $index => $binding) {
            $type = gettype($binding);
            switch ($type) {
            case 'boolean':
                $stmt->bindValue($index + 1, $binding, \PDO::PARAM_BOOL);
                break;
            case 'integer':
                $stmt->bindValue($index + 1, $binding, \PDO::PARAM_INT);
                break;
            case 'double':
            case 'string':
                $stmt->bindValue($index + 1, $binding, \PDO::PARAM_STR);
                break;
            case 'NULL':
                $stmt->bindValue($index + 1, $binding, \PDO::PARAM_NULL);
                break;
            default:
                throw new \UnexpectedValueException("The value does not be bindable, got '$type'.");
            }
        }

        return $stmt;
    }

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
     * @return Sql
     */
    abstract public function build();
}
