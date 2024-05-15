<?php

declare(strict_types=1);

namespace Emonkak\Orm;

use Emonkak\Database\PDOInterface;

trait Aggregatable
{
    public function avg(PDOInterface $pdo, string $expr): int
    {
        return (int) $this->aggregate($pdo, "AVG($expr)");
    }

    public function count(PDOInterface $pdo, string $expr = '*'): int
    {
        return (int) $this->aggregate($pdo, "COUNT($expr)");
    }

    public function max(PDOInterface $pdo, string $expr): int
    {
        return (int) $this->aggregate($pdo, "MAX($expr)");
    }

    public function min(PDOInterface $pdo, string $expr): int
    {
        return (int) $this->aggregate($pdo, "MIN($expr)");
    }

    public function sum(PDOInterface $pdo, string $expr): int
    {
        return (int) $this->aggregate($pdo, "SUM($expr)");
    }

    abstract public function aggregate(PDOInterface $pdo, string $expr): mixed;
}
