<?php

namespace Emonkak\Orm\Query;

use Emonkak\Database\PDOInterface;
use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\ResultSet\PDOResultSet;
use Emonkak\Orm\Utils\PDOUtils;

trait Executable
{
    /**
     * @var string
     */
    private $class = 'stdClass';

    /**
     * @param string $class
     * @return self
     */
    public function to($class)
    {
        $chained = clone $this;
        $chained->class = $class;
        return $chained;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(PDOInterface $pdo)
    {
        list ($sql, $binds) = $this->build();

        $stmt = $pdo->prepare($sql);
        $stmt->setFetchMode(\PDO::FETCH_CLASS, $this->class);

        $this->bindTo($stmt, $binds);

        $stmt->execute();

        return new PDOResultSet($stmt);
    }

    /**
     * @param PDOStatementInterface $stmt
     * @param array                 $binds
     */
    private function bindTo(PDOStatementInterface $stmt, array $binds)
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
