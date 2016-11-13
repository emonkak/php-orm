<?php

namespace Emonkak\Orm;

use Emonkak\Orm\Relation\RelationInterface;

trait Relatable
{
    /**
     * @param RelationInterface $relation
     * @return SelectQuery
     */
    public function with(RelationInterface $relation)
    {
        return $this->observe(function(QueryInterface $query) use ($relation) {
            return new RelationQuery($query, $relation);
        });
    }

    /**
     * @param callable $observer
     * @return $this
     */
    abstract public function observe(callable $observer);
}
