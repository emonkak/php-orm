<?php

namespace Emonkak\Orm\Query;

use Emonkak\Orm\Relation\RelationInterface;
use Emonkak\QueryBuilder\SelectQueryBuilder;

class SelectQuery extends SelectQueryBuilder implements ExecutableQueryInterface
{
    use Executable, Observable {
        Observable::execute insteadof Executable;
    }

    /**
     * @param RelationInterface $relation
     * @param callable          $constraint
     * @return self
     */
    public function with(RelationInterface $relation, callable $constraint = null)
    {
        return $this->observe(function($query) use ($relation, $constraint) {
            return new RelationQuery($this->class, $query, $relation, $constraint ?: function($query) {
                return $query;
            });
        });
    }
}
