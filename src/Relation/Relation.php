<?php

namespace Emonkak\Orm\Relation;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\ResultSet\ResultSetInterface;
use Emonkak\Orm\SelectQuery;

abstract class Relation implements RelationInterface
{
    /**
     * @var string
     */
    protected $class;

    /**
     * @var string
     */
    protected $table;

    /**
     * @var string
     */
    protected $relationKey;

    /**
     * @var string
     */
    protected $outerKey;

    /**
     * @var string
     */
    protected $innerKey;

    /**
     * @var PDOInterface
     */
    protected $connection;

    /**
     * @var SelectQuery
     */
    protected $query;

    /**
     * @param string       $class
     * @param string       $table
     * @param string       $relationKey
     * @param string       $outerKey
     * @param string       $innerKey
     * @param PDOInterface $connection
     * @param SelectQuery  $query
     */
    public function __construct(
        $class,
        $table,
        $relationKey,
        $outerKey,
        $innerKey,
        PDOInterface $connection,
        SelectQuery $query = null
    ) {
        $this->class = $class;
        $this->table = $table;
        $this->relationKey = $relationKey;
        $this->outerKey = $outerKey;
        $this->innerKey = $innerKey;
        $this->connection = $connection;
        $this->query = $query ?: new SelectQuery();
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
     * @return PDOInterface
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @return SelectQuery
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return JoinStrategyInterface
     */
    abstract public function getJoinStrategy();

    /**
     * {@inheritDoc}
     */
    public function join(ResultSetInterface $result)
    {
        $outerElements = $result->toArray();
        if (empty($outerElements)) {
            return new \EmptyIterator();
        }

        $outerClass = $result->getClass();
        $outerKeySelector = AccessorCreators::toKeySelector($this->outerKey, $outerClass);
        $innerKeySelector = AccessorCreators::toKeySelector($this->innerKey, $this->class);
        $resultSelector = AccessorCreators::toKeyAssignee($this->relationKey, $outerClass);
        $joinStrategy = $this->getJoinStrategy();

        $outerKeys = array_map($outerKeySelector, $outerElements);
        $innerResult = $this->getResult($outerKeys);

        return $joinStrategy(
            $outerElements,
            $innerResult,
            $outerKeySelector,
            $innerKeySelector,
            $resultSelector
        );
    }

    /**
     * {@inheritDoc}
     */
    public function with(RelationInterface $relation)
    {
        return new static(
            $this->class,
            $this->table,
            $this->relationKey,
            $this->outerKey,
            $this->innerKey,
            $this->connection,
            $this->query->with($relation)
        );
    }

    /**
     * @param mixed[] $outerKeys
     * @return ResultSetInterface
     */
    protected function getResult($outerKeys)
    {
        return $this->query
            ->from(sprintf('`%s`', $this->table))
            ->where(sprintf('`%s`.`%s`', $this->table, $this->innerKey), 'IN', $outerKeys)
            ->getResult($this->connection, $this->class);
    }
}
