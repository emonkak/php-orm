<?php

namespace Emonkak\Orm\Relation;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\SelectBuilder;

class OneTo implements RelationStrategyInterface
{
    /**
     * @var string
     */
    private $relationKey;

    /**
     * @var string
     */
    private $table;

    /**
     * @var string
     */
    private $outerKey;

    /**
     * @var string
     */
    private $innerKey;

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
     * @param string                      $table
     * @param string                      $outerKey
     * @param string                      $innerKey
     * @param PDOInterface                $pdo
     * @param FetcherInterface            $fetcher
     * @param SelectBuilder               $queryBuilder
     * @param array<string,SelectBuilder> $unions
     */
    public function __construct(
        $relationKey,
        $table,
        $outerKey,
        $innerKey,
        PDOInterface $pdo,
        FetcherInterface $fetcher,
        SelectBuilder $queryBuilder,
        array $unions
    ) {
        $this->relationKey = $relationKey;
        $this->table = $table;
        $this->outerKey = $outerKey;
        $this->innerKey = $innerKey;
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
        $queryBuilder = $this->createQueryBuilderFrom($this->queryBuilder, $this->table, $outerKeys);

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
        return AccessorCreators::toKeySelector($this->outerKey, $outerClass);
    }

    /**
     * {@inheritDoc}
     */
    public function getInnerKeySelector($innerClass)
    {
        return AccessorCreators::toKeySelector($this->innerKey, $innerClass);
    }

    /**
     * {@inheritDoc}
     */
    public function getResultSelector($outerClass, $innerClass)
    {
        return AccessorCreators::toKeyAssignee($this->relationKey, $outerClass);
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
            ->where(
                $grammar->identifier($table) . '.' . $grammar->identifier($this->innerKey),
                'IN',
                $outerKeys
            );

        return $queryBuilder;
    }
}
