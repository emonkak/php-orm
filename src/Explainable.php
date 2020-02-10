<?php

declare(strict_types=1);

namespace Emonkak\Orm;

use Emonkak\Database\PDOInterface;

trait Explainable
{
    public function explain(PDOInterface $pdo): array
    {
        $stmt = $this->build()->prepend('EXPLAIN')->prepare($pdo);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    abstract public function build(): Sql;
}
