<?php

namespace Emonkak\Orm\Query;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\Relation\RelationInterface;
use Emonkak\Orm\ResultSet\IteratorResultSet;
use Emonkak\QueryBuilder\QueryInterface;

class RelationQuery implements QueryInterface
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
     * @var RelationQueryBuilderInterface
     */
    private $relationQueryBuilder;

    /**
     * @var callable
     */
    private $callback;

    /**
     * @param ExecutableQueryInterface $outerQuery
     * @param RelationInterface        $relation
    */
    public static function create(ExecutableQueryInterface $outerQuery, RelationInterface $relation)
    {
        return new self($outerQuery, $relation, RelationQueryBuilder::getInstance());
    }

    /**
     * @param oxexutableQueryInterface      $outerQuery
     * @param RelationInterface             $relation
     * @param RelationQueryBuilderInterface $relationQueryBuilder
    */
    public function __construct(
        ExecutableQueryInterface $outerQuery,
        RelationInterface $relation,
        RelationQueryBuilderInterface $relationQueryBuilder
    ) {
        $this->outerQuery = $outerQuery;
        $this->relation = $relation;
        $this->relationQueryBuilder = $relationQueryBuilder;
    }

    /**
     * @param callable $callback (query: ExecutableQueryInterface, outerValues: mixed[]) -> ExecutableQueryInterface
     * @return self
     */
    public function withCallback(callable $callback)
    {
        $this->callback = $callback;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function compile()
    {
        return $this->outerQuery->compile();
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

        $relation = $this->relation;
        $callback = $this->callback;

        $innerQuery = $this->relationQueryBuilder->build($outerValues, $relation);

        if ($callback) {
            $innerQuery = $callback($innerQuery, $outerValues);
        }

        $result = $relation->join($outerValues, $innerQuery->execute($pdo));

        return new IteratorResultSet($result);
    }

    /**
     * {@inheritDoc}
     */
    public function __toString()
    {
        return (string) $this->outerQuery;
    }
}
