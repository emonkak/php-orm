<?php

namespace Emonkak\Orm\Query;

use Emonkak\Orm\Relation\RelationInterface;

interface RelationQueryBuilderInterface
{
    /**
     * @param array             $outerValues
     * @param RelationInterface $relation
     * @return ExecutableQueryInterface
     */
    public function build(array $outerValues, RelationInterface $relation);
}
