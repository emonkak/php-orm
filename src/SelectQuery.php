<?php

namespace Emonkak\Orm;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\QueryBuilder\SelectBuilder;
use Emonkak\Orm\Fetcher\FetcherInterface;

class SelectQuery extends SelectBuilder
{
    use Preparable;
    use Fetchable;

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
        $stmt = $this->withSelect(["$func($expr)"])->prepare($connection);
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
