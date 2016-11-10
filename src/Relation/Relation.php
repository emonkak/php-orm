<?php

namespace Emonkak\Orm\Relation;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\Relation\JoinStrategy\GroupJoinStrategy;
use Emonkak\Orm\Relation\JoinStrategy\JoinStrategyInterface;
use Emonkak\Orm\Relation\JoinStrategy\LazyGroupJoinStrategy;
use Emonkak\Orm\Relation\JoinStrategy\LazyInnerJoinStrategy;
use Emonkak\Orm\Relation\JoinStrategy\OuterJoinStrategy;
use Emonkak\Orm\ResultSet\ResultSetInterface;
use Emonkak\Orm\SelectQuery;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;

class Relation implements RelationInterface
{
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
     * @var JoinStrategyInterface
     */
    private $joinStrategy;

    /**
     * @var PDOInterface
     */
    private $connection;

    /**
     * @var SelectQuery
     */
    private $query;

    /**
     * @param string           $class
     * @param string           $table
     * @param string           $relationKey
     * @param string           $outerKey
     * @param string           $innerKey
     * @param PDOInterface     $connection
     * @param SelectQuery|null $query
     */
    public static function oneToOne(
        $class,
        $table,
        $relationKey,
        $outerKey,
        $innerKey,
        PDOInterface $connection,
        SelectQuery $query = null
    ) {
        return new Relation(
            $class,
            $table,
            $relationKey,
            $outerKey,
            $innerKey,
            new OuterJoinStrategy(),
            $connection,
            $query ?: new SelectQuery()
        );
    }

    /**
     * @param string           $class
     * @param string           $table
     * @param string           $relationKey
     * @param string           $outerKey
     * @param string           $innerKey
     * @param PDOInterface     $connection
     * @param SelectQuery|null $query
     */
    public static function oneToMany(
        $class,
        $table,
        $relationKey,
        $outerKey,
        $innerKey,
        PDOInterface $connection,
        SelectQuery $query = null
    ) {
        return new Relation(
            $class,
            $table,
            $relationKey,
            $outerKey,
            $innerKey,
            new GroupJoinStrategy(),
            $connection,
            $query ?: new SelectQuery()
        );
    }

    /**
     * @param string                        $class
     * @param string                        $table
     * @param string                        $relationKey
     * @param string                        $outerKey
     * @param string                        $innerKey
     * @param LazyLoadingValueHolderFactory $proxyFactory
     * @param PDOInterface                  $connection
     * @param SelectQuery|null              $query
     */
    public static function lazyOneToOne(
        $class,
        $table,
        $relationKey,
        $outerKey,
        $innerKey,
        LazyLoadingValueHolderFactory $proxyFactory,
        PDOInterface $connection,
        SelectQuery $query = null
    ) {
        return new Relation(
            $class,
            $table,
            $relationKey,
            $outerKey,
            $innerKey,
            new LazyInnerJoinStrategy($proxyFactory),
            $connection,
            $query ?: new SelectQuery()
        );
    }

    /**
     * @param string                        $class
     * @param string                        $table
     * @param string                        $relationKey
     * @param string                        $outerKey
     * @param string                        $innerKey
     * @param LazyLoadingValueHolderFactory $proxyFactory
     * @param PDOInterface                  $connection
     * @param SelectQuery|null              $query
     */
    public static function lazyOneToMany(
        $class,
        $table,
        $relationKey,
        $outerKey,
        $innerKey,
        LazyLoadingValueHolderFactory $proxyFactory,
        PDOInterface $connection,
        SelectQuery $query = null
    ) {
        return new Relation(
            $class,
            $table,
            $relationKey,
            $outerKey,
            $innerKey,
            new LazyGroupJoinStrategy($proxyFactory),
            $connection,
            $query ?: new SelectQuery()
        );
    }

    /**
     * @param string                $class
     * @param string                $table
     * @param string                $relationKey
     * @param string                $outerKey
     * @param string                $innerKey
     * @param JoinStrategyInterface $joinStrategy
     * @param PDOInterface          $connection
     * @param SelectQuery           $query
     */
    public function __construct(
        $class,
        $table,
        $relationKey,
        $outerKey,
        $innerKey,
        JoinStrategyInterface $joinStrategy,
        PDOInterface $connection,
        SelectQuery $query
    ) {
        $this->class = $class;
        $this->table = $table;
        $this->relationKey = $relationKey;
        $this->outerKey = $outerKey;
        $this->innerKey = $innerKey;
        $this->joinStrategy = $joinStrategy;
        $this->connection = $connection;
        $this->query = $query;
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
        $innerKeySelector = AccessorCreators::toKeySelector($this->innerKey, $this->class);
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
            $this->class,
            $this->table,
            $this->relationKey,
            $this->outerKey,
            $this->innerKey,
            $this->joinStrategy,
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
