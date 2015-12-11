<?php

namespace Emonkak\Orm\Relation;

use Emonkak\Orm\Query\QueryInterface;
use Emonkak\Orm\ResultSet\ResultSetInterface;

interface RelationInterface
{
    /**
     * Joins between outer values and inner values.
     *
     * @param string  $outerClass
     * @param mixed[] $outerValues
     * @param mixed[] $innerValues
     * @return \Iterator
     */
    public function join($outerClass, array $outerValues, array $innerValues);

    /**
     * Builds the query to fetch inner values.
     *
     * @param string  $outerClass
     * @param mixed[] $outerValues
     * @return QueryInterface
     */
    public function buildQuery($outerClass, array $outerValues);

    /**
     * Executes the query.
     *
     * @param QueryInterface $query
     * @return ResultSetInterface
     */
    public function executeQuery(QueryInterface $query);
}
