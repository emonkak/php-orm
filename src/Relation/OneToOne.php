<?php

namespace Emonkak\Orm\Relation;

use Emonkak\Collection\Collection;
use Emonkak\Database\PDOInterface;
use Emonkak\Orm\ResultSet\ResultSetInterface;
use Emonkak\Orm\SelectQuery;

class OneToOne extends AbstractRelation
{
    /**
     * @var PDOInterface
     */
    private $connection;

    /**
     * @var SelectQuery
     */
    private $query;

    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $table;

    /**
     * @var string
     */
    private $relationKey;

    /**
     * @var string
     */
    private $outerKey;

    /**
     * @var string
     */
    private $innerKey;

    /**
     * @param PDOInterface $connection
     * @param SelectQuery  $query
     * @param string       $class
     * @param string       $table
     * @param string       $relationKey
     * @param string       $outerKey
     * @param string       $innerKey
     */
    public function __construct(
        PDOInterface $connection,
        SelectQuery $query,
        $class,
        $table,
        $relationKey,
        $outerKey,
        $innerKey
    ) {
        $this->connection = $connection;
        $this->query = $query;
        $this->class = $class;
        $this->table = $table;
        $this->relationKey = $relationKey;
        $this->outerKey = $outerKey;
        $this->innerKey = $innerKey;
    }

    /**
     * {@inheritDoc}
     */
    public function with(RelationInterface $relation)
    {
        return new OneToOne(
            $this->connection,
            $this->query->with($relation, $connection),
            $this->class,
            $this->table,
            $this->relationKey,
            $this->outerKey,
            $this->innerKey
        );
    }

    /**
     * @return PDOInterface
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * @return string
     */
    public function getRelationKey()
    {
        return $this->relationKey;
    }

    /**
     * @return string
     */
    public function getOuterKey()
    {
        return $this->outerKey;
    }

    /**
     * @return string
     */
    public function getInnerKey()
    {
        return $this->innerKey;
    }

    /**
     * {@inheritDoc}
     */
    protected function getResult(array $outerValues, $outerClass)
    {
        $outerKeySelector = AccessorCreators::toKeySelector($this->outerKey, $outerClass);
        $outerKeys = array_map($outerKeySelector, $outerValues);

        return $this->query
            ->from(sprintf('`%s`', $this->table))
            ->where(sprintf('`%s`.`%s`', $this->table, $this->innerKey), 'IN', $outerKeys)
            ->getResult($this->connection, $this->class);
    }

    /**
     * {@inheritDoc}
     */
    protected function doJoin(array $outerValues, $outerClass, ResultSetInterface $inner)
    {
        $innerValues = $inner->all();
        $outerKeySelector = AccessorCreators::toKeySelector($this->outerKey, $outerClass);
        $innerKeySelector = AccessorCreators::toKeySelector($this->innerKey, $this->class);
        $resultValueSelector = AccessorCreators::toKeyAssignee($this->relationKey, $outerClass);

        if (count($outerValues) > count($innerValues)) {
            $collection = Collection::from($outerValues)->outerJoin(
                $innerValues,
                $outerKeySelector,
                $innerKeySelector,
                $resultValueSelector
            );
        } else {
            $collection = Collection::from($inner->all())->outerJoin(
                $outerValues,
                $innerKeySelector,
                $outerKeySelector,
                static function($outerValue, $innerValue) use ($resultValueSelector) {
                    return $resultValueSelector($innerValue, $outerValue);
                }
            );
        }

        return $collection->getIterator();
    }
}
