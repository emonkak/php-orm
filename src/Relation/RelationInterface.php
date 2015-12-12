<?php

namespace Emonkak\Orm\Relation;

use Emonkak\Orm\ExecutableQueryInterface;
use Emonkak\Orm\ResultSet\ResultSetInterface;

interface RelationInterface
{
    /**
     * Joins between outer values and inner values.
     *
     * @param mixed[] $outerValues
     * @param mixed[] $innerValues
     * @param string  $outerClass
     * @return \Iterator
     */
    public function join(array $outerValues, array $innerValues, $outerClass);

    /**
     * Builds the query to fetch inner values.
     *
     * @param mixed[] $outerValues
     * @param string  $outerClass
     * @return ExecutableQueryInterface
     */
    public function buildQuery(array $outerValues, $outerClass);

    /**
     * Adds the relation to this relation.
     *
     * @param RelationInterface $relation
     * @return self
     */
    public function with(RelationInterface $relation);

    /**
     * @return string
     */
    public function getClass();

    /**
     * @return string
     */
    public function getTable();

    /**
     * @return string
     */
    public function getRelationKey();

    /**
     * @return string
     */
    public function getOuterKey();

    /**
     * @return string
     */
    public function getInnerKey();
}
