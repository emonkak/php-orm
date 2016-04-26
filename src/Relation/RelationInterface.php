<?php

namespace Emonkak\Orm\Relation;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\ExecutableQueryInterface;
use Emonkak\Orm\ResultSet\ResultSetInterface;

interface RelationInterface
{
    /**
     * Joins between outer values and inner values.
     *
     * @param ResultSetInterface $outer
     * @param string             $outerClass
     * @return ResultSetInterface
     */
    public function join(ResultSetInterface $outer, $outerClass);

    /**
     * Adds the relation to this relation.
     *
     * @param RelationInterface $relation
     * @param PDOInterface|null $connection
     * @return self
     */
    public function with(RelationInterface $relation);
}
