<?php

namespace Emonkak\Orm\Relation;

use Emonkak\Orm\ResultSet\ResultSetInterface;

interface RelationInterface
{
    /**
     * Joins between the outer result and the relation result.
     *
     * @param ResultSetInterface  $result
     * @return \Traversable
     */
    public function join(ResultSetInterface $result);

    /**
     * Adds the relation to this relation.
     *
     * @param RelationInterface $relation
     * @return RelationInterface
     */
    public function with(RelationInterface $relation);
}
