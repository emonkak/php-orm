<?php

namespace Emonkak\Orm;

use Emonkak\Database\PDOInterface;
use Emonkak\Database\PDOStatementInterface;

/**
 * @internal
 */
trait Explainable
{
    /**
     * @param PDOInterface $connection
     * @return array
     */
    public function explain(PDOInterface $connection)
    {
        $stmt = $this->build()->prepend('EXPLAIN')->prepare($connection);
        $stmt->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @return Sql
     */
    abstract public function build();
}
