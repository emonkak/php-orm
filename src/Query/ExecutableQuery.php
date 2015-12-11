<?php

namespace Emonkak\Orm\Query;

use Emonkak\Database\PDOInterface;
use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\ResultSet\PDOResultSet;
use Emonkak\QueryBuilder\QueryBuilderInterface;

class ExecutableQuery implements ExecutableQueryInterface
{
    /**
     * @var QueryInterface
     */
    private $query;

    /**
     * @var string
     */
    private $class;

    /**
     * @param QueryBuilderInterface $query
     * @param string                $class
     */
    public function __construct(QueryBuilderInterface $query, $class)
    {
        $this->query = $query;
        $this->class = $class;
    }

    /**
     * {@inheritDoc}
     */
    public function __toString()
    {
        return $this->query->__toString();
    }

    /**
     * {@inheritDoc}
     */
    public function build()
    {
        return $this->query->build();
    }

    /**
     * {@inheritDoc}
     */
    public function execute(PDOInterface $pdo)
    {
        list ($sql, $binds) = $this->query->build();

        $stmt = $pdo->prepare($sql);
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
}
