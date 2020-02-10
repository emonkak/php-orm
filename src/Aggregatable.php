<?php

declare(strict_types=1);

namespace Emonkak\Orm;

use Emonkak\Database\PDOInterface;

trait Aggregatable
{
    public function avg(PDOInterface $pdo, $expr): int
    {
        return (int) $this->aggregate($pdo, "AVG($expr)");
    }

    public function count(PDOInterface $pdo, $expr = '*'): int
    {
        return (int) $this->aggregate($pdo, "COUNT($expr)");
    }

    public function max(PDOInterface $pdo, $expr): int
    {
        return (int) $this->aggregate($pdo, "MAX($expr)");
    }

    public function min(PDOInterface $pdo, $expr): int
    {
        return (int) $this->aggregate($pdo, "MIN($expr)");
    }

    public function sum(PDOInterface $pdo, $expr): int
    {
        return (int) $this->aggregate($pdo, "SUM($expr)");
    }

    abstract function aggregate(PDOInterface $pdo, $expr);
}
