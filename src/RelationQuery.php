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
    public function execute(PDOInterface $connection)
    {
        return $this->outerQuery->execute($connection);
    }

    /**
     * {@inheritDoc}
     */
    public function getResult(PDOInterface $connection, $class)
    {
        $outerValues = $this->outerQuery->getResult($connection, $class)->all();
        if (empty($outerValues)) {
            return new EmptyResultSet();
        }

        $constraint = $this->constraint;
        $innerQuery = $this->relation->buildQuery($outerValues, $class);
        $innerQuery = $constraint($innerQuery, $outerValues, $class);
        $innerClass = $this->relation->getClass();

        $innerValues = $innerQuery->getResult($this->connection, $innerClass)->all();
        $result = $this->relation->join($outerValues, $innerValues, $class);

        return new IteratorResultSet($result);
    }
}
