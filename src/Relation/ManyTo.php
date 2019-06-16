<?php

namespace Emonkak\Orm\Relation;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\Relation\JoinStrategy\JoinStrategyInterface;
use Emonkak\Orm\ResultSet\PreloadedResultSet;
use Emonkak\Orm\ResultSet\ResultSetInterface;
use Emonkak\Orm\SelectBuilder;

class ManyTo implements RelationStrategyInterface
{
    /**
     * @var string
     */
    private $relationKey;

    /**
     * @var string
     */
    private $oneToManyTable;

    /**
     * @var string
     */
    private $oneToManyOuterKey;

    /**
     * @var string
     */
    private $oneToManyInnerKey;

    /**
     * @var string
     */
    private $manyToOneTable;

    /**
     * @var string
     */
    private $manyToOneOuterKey;

    /**
     * @var string
     */
    private $manyToOneInnerKey;

    /**
     * @var PDOInterface
     */
    private $pdo;

    /**
     * @var FetcherInterface
     */
    private $fetcher;

    /**
     * @var SelectBuilder
     */
    private $builder;

    /**
     * @var array<string,SelectBuilder>
     */
    private $unions;

    /**
     * @param string                      $relationKey
     * @param string                      $oneToManyTable
     * @param string                      $oneToManyOuterKey
     * @param string                      $oneToManyInnerKey
     * @param string                      $manyToOneTable
     * @param string                      $manyToOneOuterKey
     * @param string                      $manyToOneInnerKey
     * @param PDOInterface                $pdo
     * @param FetcherInterface            $fetcher
     * @param SelectBuilder               $builder
     * @param array<string,SelectBuilder> $unions
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
        array $unions
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
        $this->unions = $unions;
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
     * @return PDOInterface
     */
    public function getPdo()
    {
        return $this->pdo;
    }

    /**
     * @return FetcherInterface
     */
    public function getFetcher()
    {
        return $this->fetcher;
    }

    /**
     * @return SelectBuilder
     */
    public function getBuilder()
    {
        return $this->builder;
    }

    /**
     * @return array<string,SelectBuilder>
     */
    public function getUnions()
    {
        return $this->unions;
    }

    /**
     * {@inheritDoc}
     */
    public function getResult(array $outerKeys)
    {
        $grammar = $this->builder->getGrammar();

        $builder = $this->getBuilderFrom($this->builder, $this->manyToOneTable, $outerKeys);

        foreach ($this->unions as $unionTable => $unionBuilder) {
            $unionBuilder = $this->getBuilderFrom($unionBuilder, $unionTable, $outerKeys);

            $builder = $builder->unionAllWith($unionBuilder);
        }

        return $builder
            ->getResult($this->pdo, $this->fetcher);
    }

    /**
     * {@inheritDoc}
     */
    public function getOuterKeySelector($outerClass)
    {
        return AccessorCreators::toKeySelector($this->oneToManyOuterKey, $outerClass);
    }

    /**
     * {@inheritDoc}
     */
    public function getInnerKeySelector($innerClass)
    {
        return AccessorCreators::toPivotKeySelector($this->getPivotKey(), $innerClass);
    }

    /**
     * {@inheritDoc}
     */
    public function getResultSelector($outerClass, $innerClass)
    {
        return AccessorCreators::toKeyAssignee($this->relationKey, $outerClass);
    }

    /**
     * @return string
     */
    private function getPivotKey()
    {
        return '__pivot_' . $this->oneToManyInnerKey;
    }

    private function getBuilderFrom(SelectBuilder $builder, string $table, array $outerKeys): SelectBuilder
    {
        $grammar = $this->builder->getGrammar();

        if (count($builder->getFrom()) === 0) {
            $builder = $builder->from($grammar->identifier($table));
        }

        $builder = $builder
            ->outerJoin(
                $grammar->identifier($this->oneToManyTable),
                sprintf(
                    '%s.%s = %s.%s',
                    $grammar->identifier($table),
                    $grammar->identifier($this->manyToOneInnerKey),
                    $grammar->identifier($this->oneToManyTable),
                    $grammar->identifier($this->manyToOneOuterKey)
                )
            )
            ->where(
                $grammar->identifier($this->oneToManyTable) . '.' . $grammar->identifier($this->oneToManyInnerKey),
                'IN',
                $outerKeys
            );

        if (count($builder->getSelect()) === 0) {
            $builder = $builder
                ->select($grammar->identifier($table) . '.*');
        }

        return $builder
            ->select(
                $grammar->identifier($this->oneToManyTable) . '.' . $grammar->identifier($this->oneToManyInnerKey),
                $grammar->identifier($this->getPivotKey())
            );
    }
}
