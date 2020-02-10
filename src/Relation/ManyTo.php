<?php

declare(strict_types=1);

namespace Emonkak\Orm\Relation;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\Fetcher\FetcherInterface;
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
    private $queryBuilder;

    /**
     * @var array<string,SelectBuilder>
     */
    private $unions;

    /**
     * @param PDOInterface $pdo
     * @param FetcherInterface $fetcher
     * @param SelectBuilder $queryBuilder
     * @param array<string,SelectBuilder> $unions
     */
    public function __construct(
        string $relationKey,
        string $oneToManyTable,
        string $oneToManyOuterKey,
        string $oneToManyInnerKey,
        string $manyToOneTable,
        string $manyToOneOuterKey,
        string $manyToOneInnerKey,
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

    public function getRelationKey(): string
    {
        return $this->relationKey;
    }

    public function getOneToManyTable(): string
    {
        return $this->oneToManyTable;
    }

    public function getOneToManyOuterKey(): string
    {
        return $this->oneToManyOuterKey;
    }

    public function getOneToManyInnerKey(): string
    {
        return $this->oneToManyInnerKey;
    }

    public function getManyToOneTable(): string
    {
        return $this->manyToOneTable;
    }

    public function getManyToOneOuterKey(): string
    {
        return $this->manyToOneOuterKey;
    }

    public function getManyToOneInnerKey(): string
    {
        return $this->manyToOneInnerKey;
    }

    public function getPdo(): PDOInterface
    {
        return $this->pdo;
    }

    public function getFetcher(): FetcherInterface
    {
        return $this->fetcher;
    }

    public function getQueryBuilder(): SelectBuilder
    {
        return $this->queryBuilder;
    }

    /**
     * @return array<string,SelectBuilder>
     */
    public function getUnions(): array
    {
        return $this->unions;
    }

    public function getResult(array $outerKeys): ResultSetInterface
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

    public function getOuterKeySelector(?string $outerClass): callable
    {
        return AccessorCreators::createKeySelector($this->oneToManyOuterKey, $outerClass);
    }

    public function getInnerKeySelector(?string $innerClass): callable
    {
        return AccessorCreators::createPivotKeySelector($this->getPivotKey(), $innerClass);
    }

    public function getResultSelector(?string $outerClass, ?string $innerClass): callable
    {
        return AccessorCreators::createKeyAssignee($this->relationKey, $outerClass);
    }

    private function getPivotKey(): string
    {
        return '__pivot_' . $this->oneToManyInnerKey;
    }

    /**
     * @param mixed[] $outerKeys
     */
    private function createQueryBuilderFrom(SelectBuilder $queryBuilder, string $table, array $outerKeys): SelectBuilder
    {
        $grammar = $this->queryBuilder->getGrammar();

        if (count($queryBuilder->getSelectBuilder()) === 0) {
            $queryBuilder = $queryBuilder
                ->select($grammar->identifier($table) . '.*');
        }

        $queryBuilder = $queryBuilder
            ->select(
                $grammar->identifier($this->oneToManyTable) . '.' . $grammar->identifier($this->oneToManyInnerKey),
                $grammar->identifier($this->getPivotKey())
            );

        if (count($queryBuilder->getFrom()) === 0) {
            $queryBuilder = $queryBuilder->from($grammar->identifier($table));
        }

        return $queryBuilder
            ->outerJoin(
                $grammar->identifier($this->oneToManyTable),
                sprintf(
                    '%s.%s = %s.%s',
                    $grammar->identifier($table),
                    $grammar->identifier($this->manyToOneInnerKey),
                    $grammar->identifier($this->oneToManyTable),
                    $grammar->identifier($this->manyToOneOuterKey)
                ),
                null,
                0
            )
            ->where(
                $grammar->identifier($this->oneToManyTable) . '.' . $grammar->identifier($this->oneToManyInnerKey),
                'IN',
                $outerKeys
            );
    }
}
