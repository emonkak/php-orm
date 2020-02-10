<?php

declare(strict_types=1);

namespace Emonkak\Orm\Relation;

use Emonkak\Orm\ResultSet\ResultSetInterface;

interface RelationInterface
{
    /**
     * Associates between the outer result and the relation result.
     */
    public function associate(ResultSetInterface $result): \Traversable;
}
