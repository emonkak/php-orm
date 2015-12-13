<?php

namespace Emonkak\Orm;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\Relation\RelationInterface;
use Emonkak\Orm\ResultSet\IteratorResultSet;
use Emonkak\Orm\ResultSet\EmptyResultSet;

class RelationQuery implements ExecutableQueryInterface
{
    /**
     * @var ExecutableQueryInterface
     */
    private $outerQuery;

    /**
     * @var PDOInterface
     */
    private $connection;

    /**
     * @var RelationInterface
     */
    private $relation;

    /**
     * @var callable
     */
    private $constraint;

    /**
     * @param ExecutableQueryInterface $outerQuery
     * @param PDOInterface             $connection
     * @param RelationInterface        $relation
     * @param callable                 $constraint (query: ExecutableQueryInterface, outerValues: mixed[], outerClass: string) -> ExecutableQueryInterface
     */
    public function __construct(
        ExecutableQueryInterface $outerQuery,
        PDOInterface $connection,
        RelationInterface $relation,
        callable $constraint
    ) {
        $this->outerQuery = $outerQuery;
        $this->connection = $connection;
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
    public function getClass()
    {
        return $this->outerQuery->getClass();
    }

    /**
     * {@inheritDoc}
     */
    public function execute(PDOInterface $connection)
    {
        $outerValues = $this->outerQuery->execute($connection)->all();
        if (empty($outerValues)) {
            return new EmptyResultSet();
        }

        $outerClass = $this->outerQuery->getClass();
        $constraint = $this->constraint;
        $relation = $this->relation;

        $innerQuery = $relation->buildQuery($outerValues, $outerClass);
        $innerQuery = $constraint($innerQuery, $outerValues, $outerClass);
        $innerValues = $innerQuery->execute($this->connection)->all();

        $result = $relation->join($outerValues, $innerValues, $outerClass);

        return new IteratorResultSet($result);
    }
}
