<?php

declare(strict_types=1);

namespace Emonkak\Orm\Relation;

use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\Relation\JoinStrategy\JoinStrategyInterface;
use Emonkak\Orm\SelectBuilder;

/**
 * @template TInner
 * @template TKey
 * @implements RelationStrategyInterface<TInner,TKey>
 */
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
     * @var string
     */
    private $pivotKey;

    /**
     * @var SelectBuilder
     */
    private $queryBuilder;

    /**
     * @psalm-var FetcherInterface<TInner>
     * @var FetcherInterface
     */
    private $fetcher;

    /**
     * @var array<string,SelectBuilder>
     */
    private $unions;

    /**
     * @psalm-param FetcherInterface<TInner> $fetcher
     * @psalm-param array<string,SelectBuilder> $unions
     */
    public function __construct(
        string $relationKey,
        string $oneToManyTable,
        string $oneToManyOuterKey,
        string $oneToManyInnerKey,
        string $manyToOneTable,
        string $manyToOneOuterKey,
        string $manyToOneInnerKey,
        string $pivotKey,
        SelectBuilder $queryBuilder,
        FetcherInterface $fetcher,
        array $unions
    ) {
        $this->relationKey = $relationKey;
        $this->oneToManyTable = $oneToManyTable;
        $this->oneToManyOuterKey = $oneToManyOuterKey;
        $this->oneToManyInnerKey = $oneToManyInnerKey;
        $this->manyToOneTable = $manyToOneTable;
        $this->manyToOneOuterKey = $manyToOneOuterKey;
        $this->manyToOneInnerKey = $manyToOneInnerKey;
        $this->pivotKey = $pivotKey;
        $this->queryBuilder = $queryBuilder;
        $this->fetcher = $fetcher;
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

    public function getPivotKey(): string
    {
        return $this->pivotKey;
    }

    public function getQueryBuilder(): SelectBuilder
    {
        return $this->queryBuilder;
    }

    /**
     * @psalm-return FetcherInterface<TInner>
     */
    public function getFetcher(): FetcherInterface
    {
        return $this->fetcher;
    }

    /**
     * @psalm-return array<string,SelectBuilder>
     */
    public function getUnions(): array
    {
        return $this->unions;
    }

    /**
     * {@inheritDoc}
     */
    public function getResult(array $outerKeys, JoinStrategyInterface $joinStrategy): iterable
    {
        $queryBuilder = $this->createQueryBuilderFrom($this->queryBuilder, $this->manyToOneTable, $outerKeys);

        foreach ($this->unions as $unionTable => $unionBuilder) {
            $unionBuilder = $this->createQueryBuilderFrom($unionBuilder, $unionTable, $outerKeys);

            $queryBuilder = $queryBuilder->unionAllWith($unionBuilder);
        }

        return $queryBuilder
            ->getResult($this->fetcher);
    }

    /**
     * @psalm-param TKey[] $outerKeys
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
