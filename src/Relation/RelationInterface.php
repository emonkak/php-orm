<?php

namespace Emonkak\Orm\Relation;

use Emonkak\Orm\ResultSet\ResultSetInterface;

interface RelationInterface
{
    /**
     * Associates between the outer result and the relation result.
     *
     * @param ResultSetInterface $result
     * @return \Traversable
     */
    public function associate(ResultSetInterface $result);
}
