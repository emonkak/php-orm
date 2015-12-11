<?php

namespace Emonkak\Orm\Query;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\Relation\RelationInterface;
use Emonkak\Orm\ResultSet\IteratorResultSet;

class RelationQuery implements ExecutableQueryInterface
{
    /**
     * @var string
     */
    private $outerClass;

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
     * @param string                   $outerClass
     * @param ExecutableQueryInterface $outerQuery
     * @param RelationInterface        $relation
     * @param callable                 $constraint (query: ExecutableQueryInterface, outerValues: mixed[]) -> ExecutableQueryInterface
     */
    public function __construct($outerClass, ExecutableQueryInterface $outerQuery, RelationInterface $relation, callable $constraint)
    {
        $this->outerClass = $outerClass;
        $this->outerQuery = $outerQuery;
        $this->relation = $relation;
        $this->constraint = $constraint;
    }

    /**
     * {@inheritDoc}
     */
    public function __toString()
    {
        return $this->outerQuery->__toString();
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

        $outerClass = $this->outerClass;
        $constraint = $this->constraint;
        $relation = $this->relation;

        $innerQuery = $constraint($relation->buildQuery($outerClass, $outerValues));
        $innerValues = $relation->executeQuery($innerQuery)->all();

        $result = $relation->join($outerClass, $outerValues, $innerValues);

        return new IteratorResultSet($result);
    }
}
