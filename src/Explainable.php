<?php

namespace Emonkak\Orm;

use Emonkak\Database\PDOInterface;

trait Explainable
{
    /**
     * @param PDOInterface $pdo
     * @return array
     */
    public function explain(PDOInterface $pdo)
    {
        $stmt = $this->build()->prepend('EXPLAIN')->prepare($pdo);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @return Sql
     */
    abstract public function build();
}
