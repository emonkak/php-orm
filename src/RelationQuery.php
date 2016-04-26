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
     * @var RelationInterface
     */
    private $relation;

    /**
     * @param ExecutableQueryInterface $outerQuery
     * @param RelationInterface        $relation
     */
    public function __construct(
        ExecutableQueryInterface $outerQuery,
        RelationInterface $relation
    ) {
        $this->outerQuery = $outerQuery;
        $this->relation = $relation;
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
        $outer = $this->outerQuery->getResult($connection, $class);

        return $this->relation->join($outer, $class);
    }
}
