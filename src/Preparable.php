<?php

namespace Emonkak\Orm;

use Emonkak\Database\PDOInterface;
use Emonkak\Database\PDOStatementInterface;

trait Preparable
{
    /**
     * @param PDOInterface $pdo
     * @return PDOStatementInterface
     */
    public function prepare(PDOInterface $pdo)
    {
        $query = $this->build();

        $stmt = $pdo->prepare($query->getSql());

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
                $typeOrClass = $type === 'object' ? get_class($binding) : $type;
                throw new \UnexpectedValueException(
                    "The value should be a bindable type. but got '$typeOrClass'."
                );
            }
        }

        return $stmt;
    }

    /**
     * @param PDOInterface $pdo
     * @return bool
     */
    public function execute(PDOInterface $pdo)
    {
        return $this->prepare($pdo)->execute();
    }

    /**
     * @return Sql
     */
    abstract public function build();
}
