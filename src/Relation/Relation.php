<?php

namespace Emonkak\Orm\Relation;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\ResultSet\ResultSetInterface;
use Emonkak\Orm\Relation\JoinStrategy\JoinStrategyInterface;
use Emonkak\Orm\SelectQuery;

class Relation implements RelationInterface
{
    /**
     * @var PDOInterface
     */
    private $connection;

    /**
     * @var FetcherInterface
     */
    private $fetcher;

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
     * @var SelectQuery
     */
    private $query;

    /**
     * @var JoinStrategyInterface
     */
    private $joinStrategy;

    /**
     * @param PDOInterface          $connection
     * @param FetcherInterface      $fetcher
     * @param string                $table
     * @param string                $relationKey
     * @param string                $outerKey
     * @param string                $innerKey
     * @param SelectQuery           $query
     * @param JoinStrategyInterface $joinStrategy
     */
    public function __construct(
        PDOInterface $connection,
        FetcherInterface $fetcher,
        $table,
        $relationKey,
        $outerKey,
        $innerKey,
        SelectQuery $query,
        JoinStrategyInterface $joinStrategy
    ) {
        $this->connection = $connection;
        $this->fetcher = $fetcher;
        $this->table = $table;
        $this->relationKey = $relationKey;
        $this->outerKey = $outerKey;
        $this->innerKey = $innerKey;
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
     * {@inheritDoc}
     */
    public function with(RelationInterface $relation)
    {
        return new Relation(
            $this->connection,
            $this->fetcher,
            $this->table,
            $this->relationKey,
            $this->outerKey,
            $this->innerKey,
            $this->query->with($relation),
            $this->joinStrategy
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
            ->getResult($this->connection, $this->fetcher);
    }
}
