<?php

namespace Emonkak\Orm\Query;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\Relation\RelationInterface;
use Emonkak\Orm\ResultSet\IteratorResultSet;

class RelationQuery implements QueryInterface
{
    /**
     * @var ExecutableQueryInterface
     */
    private $outerQuery;

    /**
     * @var RelationInterface
     */
    private $relation;

    /**
     * @var callable
     */
    private $constraint;

    /**
     * @param QueryInterface    $outerQuery
     * @param RelationInterface $relation
     * @param callable          $constraint (query: ExecutableQueryInterface, outerValues: mixed[]) -> ExecutableQueryInterface
     */
    public function __construct(QueryInterface $outerQuery, RelationInterface $relation, callable $constraint)
    {
        $this->outerQuery = $outerQuery;
        $this->relation = $relation;
        $this->constraint = $constraint;
    }

    /**
     * {@inheritDoc}
     */
    public function __toString()
    {
        return (string) $this->outerQuery;
    }

    /**
     * {@inheritDoc}
     */
    public function build()
    {
        return $this->outerQuery->build();
    }

    /**
     * {@inheritDoc}
     */
    public function execute(PDOInterface $pdo)
    {
        $outerValues = $this->outerQuery->execute($pdo)->all();
        if (empty($outerValues)) {
            return $outerValues;
        }

        $constraint = $this->constraint;
        $relation = $this->relation;

        $innerQuery = $constraint($relation->buildQuery($outerValues));
        $innerValues = $relation->executeQuery($innerQuery)->all();

        $result = $relation->join($outerValues, $innerValues);

        return new IteratorResultSet($result);
    }
}
