<?php

namespace Emonkak\Orm\Relation;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\Relation\JoinStrategy\JoinStrategyInterface;
use Emonkak\Orm\ResultSet\PreloadResultSet;
use Emonkak\Orm\ResultSet\ResultSetInterface;
use Emonkak\Orm\SelectBuilder;

class ManyToMany extends AbstractStandardRelation
{
    /**
     * @var string
     */
    protected $relationKey;

    /**
     * @var string
     */
    protected $oneToManyTable;

    /**
     * @var string
     */
    protected $oneToManyOuterKey;

    /**
     * @var string
     */
    protected $oneToManyInnerKey;

    /**
     * @var string
     */
    protected $manyToOneTable;

    /**
     * @var string
     */
    protected $manyToOneOuterKey;

    /**
     * @var string
     */
    protected $manyToOneInnerKey;

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
     * @param string                $oneToManyTable
     * @param string                $oneToManyOuterKey
     * @param string                $oneToManyInnerKey
     * @param string                $manyToOneTable
     * @param string                $manyToOneOuterKey
     * @param string                $manyToOneInnerKey
     * @param PDOInterface          $pdo
     * @param FetcherInterface      $fetcher
     * @param SelectBuilder         $builder
     * @param JoinStrategyInterface $joinStrategy
     */
    public function __construct(
        $relationKey,
        $oneToManyTable,
        $oneToManyOuterKey,
        $oneToManyInnerKey,
        $manyToOneTable,
        $manyToOneOuterKey,
        $manyToOneInnerKey,
        PDOInterface $pdo,
        FetcherInterface $fetcher,
        SelectBuilder $builder,
        JoinStrategyInterface $joinStrategy
    ) {
        $this->relationKey = $relationKey;
        $this->oneToManyTable = $oneToManyTable;
        $this->oneToManyOuterKey = $oneToManyOuterKey;
        $this->oneToManyInnerKey = $oneToManyInnerKey;
        $this->manyToOneTable = $manyToOneTable;
        $this->manyToOneOuterKey = $manyToOneOuterKey;
        $this->manyToOneInnerKey = $manyToOneInnerKey;
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
    public function getOneToManyTable()
    {
        return $this->oneToManyTable;
    }

    /**
     * @return string
     */
    public function getOneToManyOuterKey()
    {
        return $this->oneToManyOuterKey;
    }

    /**
     * @return string
     */
    public function getOneToManyInnerKey()
    {
        return $this->oneToManyInnerKey;
    }

    /**
     * @return string
     */
    public function getManyToOneTable()
    {
        return $this->manyToOneTable;
    }

    /**
     * @return string
     */
    public function getManyToOneOuterKey()
    {
        return $this->manyToOneOuterKey;
    }

    /**
     * @return string
     */
    public function getManyToOneInnerKey()
    {
        return $this->manyToOneInnerKey;
    }

    /**
     * @return string
     */
    public function getPivotKey()
    {
        return '__pivot_' . $this->oneToManyInnerKey;
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
            ->select($grammar->identifier($this->manyToOneTable) . '.*')
            ->select(
                $grammar->identifier($this->oneToManyTable) . '.' . $grammar->identifier($this->oneToManyInnerKey),
                $grammar->identifier($this->getPivotKey())
            )
            ->from($grammar->identifier($this->oneToManyTable))
            ->outerJoin(
                $grammar->identifier($this->manyToOneTable),
                sprintf(
                    '%s.%s = %s.%s',
                    $grammar->identifier($this->oneToManyTable),
                    $grammar->identifier($this->manyToOneOuterKey),
                    $grammar->identifier($this->manyToOneTable),
                    $grammar->identifier($this->manyToOneInnerKey)
                )
            )
            ->where(
                $grammar->identifier($this->oneToManyTable) . '.' . $grammar->identifier($this->oneToManyInnerKey),
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
        return AccessorCreators::toKeySelector($this->oneToManyOuterKey, $outerClass);
    }

    /**
     * {@inheritDoc}
     */
    public function resolveInnerKeySelector($innerClass)
    {
        return AccessorCreators::toPivotKeySelector($this->getPivotKey(), $innerClass);
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
        return new ManyToMany(
            $this->relationKey,
            $this->oneToManyTable,
            $this->oneToManyOuterKey,
            $this->oneToManyInnerKey,
            $this->manyToOneTable,
            $this->manyToOneOuterKey,
            $this->manyToOneInnerKey,
            $this->pdo,
            $this->fetcher,
            $this->builder->with($relation),
            $this->joinStrategy
        );
    }
}
