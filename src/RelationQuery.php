<?php

namespace Emonkak\Orm;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\ResultSet\JoinedResultSet;
use Emonkak\Orm\Relation\RelationInterface;

class RelationQuery implements QueryInterface
{
    /**
     * @var ExecutableQueryInterface
     */
    private $query;

    /**
     * @var RelationInterface
     */
    private $relation;

    /**
     * @param QueryInterface    $query
     * @param RelationInterface $relation
     */
    public function __construct(
        QueryInterface $query,
        RelationInterface $relation
    ) {
        $this->query = $query;
        $this->relation = $relation;
    }

    /**
     * {@inheritDoc}
     */
    public function __toString()
    {
        return $this->query->__toString();
    }

    /**
     * {@inheritDoc}
     */
    public function build()
    {
        return $this->query->build();
    }

    /**
     * {@inheritDoc}
     */
    public function execute(PDOInterface $connection)
    {
        return $this->query->execute($connection);
    }

    /**
     * {@inheritDoc}
     */
    public function getResult(PDOInterface $connection, $class)
    {
        $result = $this->query->getResult($connection, $class);
        return new JoinedResultSet($result, $this->relation);
    }
}
