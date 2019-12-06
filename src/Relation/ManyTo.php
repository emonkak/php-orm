<?php

namespace Emonkak\Orm\Relation;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\Fetcher\FetcherInterface;
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
    private $queryBuilder;

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
     * @param SelectBuilder               $queryBuilder
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
        SelectBuilder $queryBuilder,
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
        $this->queryBuilder = $queryBuilder;
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
    public function getQueryBuilder()
    {
        return $this->queryBuilder;
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
        $grammar = $this->queryBuilder->getGrammar();

        $queryBuilder = $this->createQueryBuilderFrom($this->queryBuilder, $this->manyToOneTable, $outerKeys);

        foreach ($this->unions as $unionTable => $unionBuilder) {
            $unionBuilder = $this->createQueryBuilderFrom($unionBuilder, $unionTable, $outerKeys);

            $queryBuilder = $queryBuilder->unionAllWith($unionBuilder);
        }

        return $queryBuilder
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

    /**
     * @param SelectBuilder $queryBuilder
     * @param string        $table
     * @param string[]      $outerKeys
     * @return SelectBuilder
     */
    private function createQueryBuilderFrom(SelectBuilder $queryBuilder, $table, $outerKeys)
    {
        $grammar = $this->queryBuilder->getGrammar();

        if (count($queryBuilder->getFrom()) === 0) {
            $queryBuilder = $queryBuilder->from($grammar->identifier($table));
        }

        $queryBuilder = $queryBuilder
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

        if (count($queryBuilder->getSelect()) === 0) {
            $queryBuilder = $queryBuilder
                ->select($grammar->identifier($table) . '.*');
        }

        return $queryBuilder
            ->select(
                $grammar->identifier($this->oneToManyTable) . '.' . $grammar->identifier($this->oneToManyInnerKey),
                $grammar->identifier($this->getPivotKey())
            );
    }
}
