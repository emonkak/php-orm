<?php

namespace Emonkak\Orm\Relation;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\ExecutableQueryInterface;

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
     * @param PDOInterface      $relationConnection
     * @param callable|null     $constraint
     * @return self
     */
    public function with(RelationInterface $relation, PDOInterface $relationConnection = null, callable $constraint = null);

    /**
     * Gets the class to map inner values.
     *
     * @return string
     */
    public function getClass();
}
