<?php

namespace Emonkak\Orm;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\QueryBuilder\SelectBuilder;

class SelectQuery extends SelectBuilder implements QueryInterface
{
    use Executable;
    use Relatable;

    /**
     * @param PDOInterface $connection
     * @param mixed        $expr
     * @return integer
     */
    public function avg(PDOInterface $connection, $expr)
    {
        return (int) $this->aggregate($connection, $expr, 'AVG');
    }

    /**
     * @param PDOInterface $connection
     * @param mixed        $expr
     * @return integer
     */
    public function count(PDOInterface $connection, $expr = '*')
    {
        return (int) $this->aggregate($connection, $expr, 'COUNT');
    }

    /**
     * @param PDOInterface $connection
     * @param mixed        $expr
     * @return integer
     */
    public function max(PDOInterface $connection, $expr)
    {
        return (int) $this->aggregate($connection, $expr, 'MAX');
    }

    /**
     * @param PDOInterface $connection
     * @param mixed        $expr
     * @return integer
     */
    public function min(PDOInterface $connection, $expr)
    {
        return (int) $this->aggregate($connection, $expr, 'MIN');
    }

    /**
     * @param PDOInterface $connection
     * @param mixed        $expr
     * @return integer
     */
    public function sum(PDOInterface $connection, $expr)
    {
        return (int) $this->aggregate($connection, $expr, 'SUM');
    }

    /**
     * @param PDOInterface $connection
     * @param mixed        $expr
     * @param string       $func
     * @return mixed
     */
    public function aggregate(PDOInterface $connection, $expr, $func)
    {
        return $this
            ->withSelect(["$func($expr)"])
            ->execute($connection)
            ->fetchColumn();
    }

    /**
     * @param PDOInterface $connection
     * @param string       $class
     * @param integer      $perPage
     * @return Paginator
     */
    public function paginate(PDOInterface $connection, $class, $perPage)
    {
        $numItems = $this->count($connection);
        return new Paginator($this, $connection, $class, $perPage, $numItems);
    }
}
