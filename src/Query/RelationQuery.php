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
     * @var callable
     */
    private $callback;

    /**
     * @param ExexutableQueryInterface $outerQuery
     * @param RelationInterface        $relation
     * @param callable                 $callback (query: ExecutableQueryInterface, outerValues: mixed[]) -> ExecutableQueryInterface
    */
    public function __construct(
        ExecutableQueryInterface $outerQuery,
        RelationInterface $relation,
        callable $callback = null
    ) {
        $this->outerQuery = $outerQuery;
        $this->relation = $relation;
        $this->callback = $callback;
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

        $callback = $this->callback;
        $relation = $this->relation;

        $innerQuery = $this->buildQuery($outerValues, $relation);

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

    /**
     * @param array             $outerValues
     * @param RelationInterface $relation
     * @return ExecutableQueryInterface
     */
    protected function buildQuery(array $outerValues, RelationInterface $relation)
    {
        $sql = sprintf(
            'SELECT * FROM `%s` WHERE `%s` IN (%s)',
            $relation->getReferenceTable(),
            $relation->getReferenceKey(),
            implode(', ', array_fill(0, count($outerValues), '?'))
        );

        $binds = array_map($relation->getOuterKeySelector(), $outerValues);

        return (new PlainQuery($sql, $binds))->withClass($relation->getClass());
    }
}
