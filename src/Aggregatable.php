<?php

namespace Emonkak\Orm;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\Fetcher\FetcherInterface;

trait Aggregatable
{
    /**
     * @param PDOInterface $pdo
     * @param mixed        $expr
     * @return integer
     */
    public function avg(PDOInterface $pdo, $expr)
    {
        return (int) $this->aggregate($pdo, "AVG($expr)");
    }

    /**
     * @param PDOInterface $pdo
     * @param mixed        $expr
     * @return integer
     */
    public function count(PDOInterface $pdo, $expr = '*')
    {
        return (int) $this->aggregate($pdo, "COUNT($expr)");
    }

    /**
     * @param PDOInterface $pdo
     * @param mixed        $expr
     * @return integer
     */
    public function max(PDOInterface $pdo, $expr)
    {
        return (int) $this->aggregate($pdo, "MAX($expr)");
    }

    /**
     * @param PDOInterface $pdo
     * @param mixed        $expr
     * @return integer
     */
    public function min(PDOInterface $pdo, $expr)
    {
        return (int) $this->aggregate($pdo, "MIN($expr)");
    }

    /**
     * @param PDOInterface $pdo
     * @param mixed        $expr
     * @return integer
     */
    public function sum(PDOInterface $pdo, $expr)
    {
        return (int) $this->aggregate($pdo, "SUM($expr)");
    }

    /**
     * @param PDOInterface $pdo
     * @param mixed        $expr
     * @return mixed
     */
    abstract function aggregate(PDOInterface $pdo, $expr);
}
