<?php

namespace Emonkak\Orm\Relation;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\ResultSet\ResultSetInterface;
use Emonkak\Orm\Relation\JoinStrategy\JoinStrategyInterface;
use Emonkak\Orm\SelectQuery;

abstract class AbstractRelation implements RelationInterface
{
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
     * @var FetcherInterface
     */
    protected $fetcher;

    /**
     * @var SelectQuery
     */
    protected $query;

    /**
     * @var JoinStrategyInterface
     */
    protected $joinStrategy;

    /**
     * @param string                $table
     * @param string                $relationKey
     * @param string                $outerKey
     * @param string                $innerKey
     * @param SelectQuery           $query
     * @param PDOInterface          $connection
     * @param FetcherInterface      $fetcher
     * @param JoinStrategyInterface $joinStrategy
     */
    public function __construct(
        $table,
        $relationKey,
        $outerKey,
        $innerKey,
        PDOInterface $connection,
        FetcherInterface $fetcher,
        SelectQuery $query,
        JoinStrategyInterface $joinStrategy
    ) {
        $this->table = $table;
        $this->relationKey = $relationKey;
        $this->outerKey = $outerKey;
        $this->innerKey = $innerKey;
        $this->connection = $connection;
        $this->fetcher = $fetcher;
        $this->query = $query;
        $this->joinStrategy = $joinStrategy;
    }

    /**
     * @return PDOInterface
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @return Fetcher
     */
    public function getFetcher()
    {
        return $this->fetcher;
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
     * @return SelectQuery
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return JoinStrategyInterface
     */
    public function getJoinStrategy()
    {
        return $this->joinStrategy;
    }

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
        $innerKeySelector = AccessorCreators::toKeySelector($this->innerKey, $this->fetcher->getClass());
        $resultSelector = AccessorCreators::toKeyAssignee($this->relationKey, $outerClass);
        $joinStrategy = $this->joinStrategy;

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
     * @param mixed[] $outerKeys
     * @return array|\Traversable
     */
    abstract protected function getResult($outerKeys);
}
