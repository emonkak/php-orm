<?php

namespace Emonkak\Orm\Relation;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\ResultSet\ResultSetInterface;
use Emonkak\Orm\Relation\JoinStrategy\JoinStrategyInterface;
use Emonkak\Orm\SelectBuilder;

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
    protected $pdo;

    /**
     * @var FetcherInterface
     */
    protected $fetcher;

    /**
     * @var SelectBuilder
     */
    protected $builder;

    /**
     * @var JoinStrategyInterface
     */
    protected $joinStrategy;

    /**
     * @param string                $table
     * @param string                $relationKey
     * @param string                $outerKey
     * @param string                $innerKey
     * @param SelectBuilder         $builder
     * @param PDOInterface          $pdo
     * @param FetcherInterface      $fetcher
     * @param JoinStrategyInterface $joinStrategy
     */
    public function __construct(
        $table,
        $relationKey,
        $outerKey,
        $innerKey,
        PDOInterface $pdo,
        FetcherInterface $fetcher,
        SelectBuilder $builder,
        JoinStrategyInterface $joinStrategy
    ) {
        $this->table = $table;
        $this->relationKey = $relationKey;
        $this->outerKey = $outerKey;
        $this->innerKey = $innerKey;
        $this->pdo = $pdo;
        $this->fetcher = $fetcher;
        $this->builder = $builder;
        $this->joinStrategy = $joinStrategy;
    }

    /**
     * @return PDOInterface
     */
    public function getPdo()
    {
        return $this->pdo;
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
     * @return SelectBuilder
     */
    public function getBuilder()
    {
        return $this->builder;
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
