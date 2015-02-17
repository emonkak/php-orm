<?php

namespace Emonkak\Orm\Utils;

use Emonkak\Database\PDOStatementInterface;

class PDOUtils
{
    private function __construct() {}

    /**
     * @param PDOStatementInterface $stmt
     * @param array                 $binds
     */
    public static function bindTo(PDOStatementInterface $stmt, array $binds)
    {
        foreach ($binds as $index => $bind) {
            switch ($type = gettype($bind)) {
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
}
