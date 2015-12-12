<?php

namespace Emonkak\Orm;

use Emonkak\Database\PDOInterface;
use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\ResultSet\PDOResultSet;

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
        $chained = $this->chained();
        $chained->class = $class;
        return $chained;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param PDOInterface $connection
     * @return ResultSetInterface
     */
    public function execute(PDOInterface $connection)
    {
        list ($sql, $binds) = $this->build();

        $stmt = $connection->prepare($sql);
        $stmt->setFetchMode(\PDO::FETCH_CLASS, $this->class);

        $this->bindTo($stmt, $binds);

        $stmt->execute();

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

    /**
     * @return self
     */
    abstract protected function chained();
}
