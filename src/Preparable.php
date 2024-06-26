<?php

declare(strict_types=1);

namespace Emonkak\Orm;

use Emonkak\Database\PDOInterface;
use Emonkak\Database\PDOStatementInterface;

trait Preparable
{
    public function prepare(PDOInterface $pdo): PDOStatementInterface
    {
        $query = $this->build();
        $sql = $query->getSql();

        $stmt = $pdo->prepare($sql);

        if ($stmt === false) {
            throw new \RuntimeException('Failed to prepare statement: ' . $sql);
        }

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
                    $typeOrClass = is_object($binding) ? get_class($binding) : $type;  // @phan-suppress-current-line PhanTypeMismatchArgumentInternal
                    throw new \UnexpectedValueException("The value should be a bindable type. but got '$typeOrClass'.");
            }
        }

        return $stmt;
    }

    public function execute(PDOInterface $pdo): bool
    {
        return $this->prepare($pdo)->execute();
    }

    abstract public function build(): Sql;
}
