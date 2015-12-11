<?php

namespace Emonkak\Orm\Query;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\Relation\RelationInterface;
use Emonkak\QueryBuilder\SelectQueryBuilder;

class SelectQuery extends SelectQueryBuilder implements QueryInterface
{
    use Executable;

    /**
     * @var array (RelationInterface, callable)[]
     */
    private $relations = [];

    /**
     * @param RelationInterface $relation
     * @param callable          $constraint
     */
    public function with(RelationInterface $relation, callable $constraint = null)
    {
        $chained = clone $this;
        $chained->relations[] = [$relation, $constraint];
        return $chained;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(PDOInterface $pdo)
    {
        $class = $this->class;
        $query = PlainQuery::fromQuery($this)->to($class);

        foreach ($this->relations as $relation) {
            list ($relation, $constraint) = $relation;

            $query = new RelationQuery($class, $query, $relation, $constraint ?: function($query) {
                return $query;
            });
        }

        return $query->execute($pdo);
    }
}
