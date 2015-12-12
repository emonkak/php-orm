<?php

namespace Emonkak\Orm;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\Relation\RelationInterface;

trait Relatable
{
    /**
     * @param RelationInterface $relation
     * @param PDOInterface      $relationConnection
     * @param callable          $constraint
     * @return self
     */
    public function with(RelationInterface $relation, PDOInterface $relationConnection = null, callable $constraint = null)
    {
        return $this->observe(function(ExecutableQueryInterface $query, PDOInterface $connection) use ($relation, $relationConnection, $constraint) {
            return new RelationQuery(
                $query,
                $relationConnection ?: $connection,
                $relation,
                $constraint ?: function($query) {
                    return $query;
                }
            );
        });
    }

    /**
     * @param callable $observer
     * @return self
     */
    abstract public function observe(callable $observer);
}
