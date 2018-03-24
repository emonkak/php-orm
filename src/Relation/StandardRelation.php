<?php

namespace Emonkak\Orm\Relation;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\Relation\JoinStrategy\JoinStrategyInterface;
use Emonkak\Orm\ResultSet\ResultSetInterface;
use Emonkak\Orm\SelectBuilder;

class StandardRelation extends AbstractStandardRelation
{
    /**
     * @var string
     */
    protected $relationKey;

    /**
     * @var string
     */
    protected $table;

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
     * @param string                $relationKey
     * @param string                $table
     * @param string                $outerKey
     * @param string                $innerKey
     * @param PDOInterface          $pdo
     * @param FetcherInterface      $fetcher
     * @param SelectBuilder         $builder
     * @param JoinStrategyInterface $joinStrategy
     */
    public function __construct(
        $relationKey,
        $table,
        $outerKey,
        $innerKey,
        PDOInterface $pdo,
        FetcherInterface $fetcher,
        SelectBuilder $builder,
        JoinStrategyInterface $joinStrategy
    ) {
        $this->relationKey = $relationKey;
        $this->table = $table;
        $this->outerKey = $outerKey;
        $this->innerKey = $innerKey;
        $this->pdo = $pdo;
        $this->fetcher = $fetcher;
        $this->builder = $builder;
        $this->joinStrategy = $joinStrategy;
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
    public function getTable()
    {
        return $this->table;
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
    public function getPdo()
    {
        return $this->pdo;
    }

    /**
     * {@inheritDoc}
     */
    public function getFetcher()
    {
        return $this->fetcher;
    }

    /**
     * {@inheritDoc}
     */
    public function getBuilder()
    {
        return $this->builder;
    }

    /**
     * {@inheritDoc}
     */
    public function getJoinStrategy()
    {
        return $this->joinStrategy;
    }

    /**
     * {@inheritDoc}
     */
    public function getResult(array $outerKeys)
    {
        $grammar = $this->builder->getGrammar();
        return $this->builder
            ->from($grammar->identifier($this->table))
            ->where(
                $grammar->identifier($this->table) . '.' . $grammar->identifier($this->innerKey),
                'IN',
                array_unique($outerKeys)
            )
            ->getResult($this->pdo, $this->fetcher);
    }

    /**
     * {@inheritDoc}
     */
    public function resolveOuterKeySelector($outerClass)
    {
        return AccessorCreators::toKeySelector($this->outerKey, $outerClass);
    }

    /**
     * {@inheritDoc}
     */
    public function resolveInnerKeySelector($innerClass)
    {
        return AccessorCreators::toKeySelector($this->innerKey, $innerClass);
    }

    /**
     * {@inheritDoc}
     */
    public function resolveResultSelector($outerClass, $innerClass)
    {
        return AccessorCreators::toKeyAssignee($this->relationKey, $outerClass);
    }

    /**
     * {@inheritDoc}
     */
    public function with(RelationInterface $relation)
    {
        return new StandardRelation(
            $this->relationKey,
            $this->table,
            $this->outerKey,
            $this->innerKey,
            $this->pdo,
            $this->fetcher,
            $this->builder->with($relation),
            $this->joinStrategy
        );
    }
}
