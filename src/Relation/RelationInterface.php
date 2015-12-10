<?php

namespace Emonkak\Orm\Relation;

use Emonkak\Orm\Query\QueryInterface;
use Emonkak\Orm\ResultSet\ResultSetInterface;

interface RelationInterface
{
    /**
     * Joins between outer values and inner values.
     *
     * @param array $outerValues
     * @param array $innerValues
     * @return \Iterator
     */
    public function join(array $outerValues, array $innerValues);

    /**
     * Builds the query to fetch inner values.
     *
     * @param array $outerValues
     * @return QueryInterface
     */
    public function buildQuery(array $outerValues);

    /**
     * Executes the query.
     *
     * @param QueryInterface $query
     * @return ResultSetInterface
     */
    public function executeQuery(QueryInterface $query);
}
