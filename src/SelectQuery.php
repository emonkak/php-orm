<?php

namespace Emonkak\Orm;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\QueryBuilder\SelectBuilder;
use Emonkak\Orm\QueryBuilder\Sql;

class SelectQuery extends SelectBuilder
{
    use Fetchable;
    use Preparable;

    /**
     * @param PDOInterface $connection
     * @param mixed        $expr
     * @return integer
     */
    public function avg(PDOInterface $connection, $expr)
    {
        return (int) $this->aggregate($connection, 'AVG', $expr);
    }

    /**
     * @param PDOInterface $connection
     * @param mixed        $expr
     * @return integer
     */
    public function count(PDOInterface $connection, $expr = '*')
    {
        return (int) $this->aggregate($connection, 'COUNT', $expr);
    }

    /**
     * @param PDOInterface $connection
     * @param mixed        $expr
     * @return integer
     */
    public function max(PDOInterface $connection, $expr)
    {
        return (int) $this->aggregate($connection, 'MAX', $expr);
    }

    /**
     * @param PDOInterface $connection
     * @param mixed        $expr
     * @return integer
     */
    public function min(PDOInterface $connection, $expr)
    {
        return (int) $this->aggregate($connection, 'MIN', $expr);
    }

    /**
     * @param PDOInterface $connection
     * @param mixed        $expr
     * @return integer
     */
    public function sum(PDOInterface $connection, $expr)
    {
        return (int) $this->aggregate($connection, 'SUM', $expr);
    }

    /**
     * @param PDOInterface $connection
     * @param string       $func
     * @param mixed        $expr
     * @return mixed
     */
    public function aggregate(PDOInterface $connection, $func, $expr)
    {
        $stmt = $this->withSelect([new Sql("$func($expr)")])->prepare($connection);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    /**
     * @param PDOInterface     $connection
     * @param FetcherInterface $fetcher
     * @param integer          $perPage
     * @return Paginator
     */
    public function paginate(PDOInterface $connection, FetcherInterface $fetcher, $perPage)
    {
        $numItems = $this->count($connection);
        return new Paginator($this, $connection, $fetcher, $perPage, $numItems);
    }
}
