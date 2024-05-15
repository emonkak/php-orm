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
    private string $relationKeyName;

    private string $oneToManyTableName;

    private string $oneToManyOuterKeyName;

    private string $oneToManyInnerKeyName;

    private string $manyToOneTableName;

    private string $manyToOneOuterKeyName;

    private string $manyToOneInnerKeyName;

    private string $pivotKey;

    private SelectBuilder $queryBuilder;

    /**
     * @var FetcherInterface<TInner>
     */
    private FetcherInterface $fetcher;

    /**
     * @param FetcherInterface<TInner> $fetcher
     */
    public function __construct(
        string $relationKeyName,
        string $oneToManyTableName,
        string $oneToManyOuterKeyName,
        string $oneToManyInnerKeyName,
        string $manyToOneTableName,
        string $manyToOneOuterKeyName,
        string $manyToOneInnerKeyName,
        string $pivotKey,
        SelectBuilder $queryBuilder,
        FetcherInterface $fetcher
    ) {
        $this->relationKeyName = $relationKeyName;
        $this->oneToManyTableName = $oneToManyTableName;
        $this->oneToManyOuterKeyName = $oneToManyOuterKeyName;
        $this->oneToManyInnerKeyName = $oneToManyInnerKeyName;
        $this->manyToOneTableName = $manyToOneTableName;
        $this->manyToOneOuterKeyName = $manyToOneOuterKeyName;
        $this->manyToOneInnerKeyName = $manyToOneInnerKeyName;
        $this->pivotKey = $pivotKey;
        $this->queryBuilder = $queryBuilder;
        $this->fetcher = $fetcher;
    }

    public function getRelationKeyName(): string
    {
        return $this->relationKeyName;
    }

    public function getOneToManyTableName(): string
    {
        return $this->oneToManyTableName;
    }

    public function getOneToManyOuterKeyName(): string
    {
        return $this->oneToManyOuterKeyName;
    }

    public function getOneToManyInnerKeyName(): string
    {
        return $this->oneToManyInnerKeyName;
    }

    public function getManyToOneTableName(): string
    {
        return $this->manyToOneTableName;
    }

    public function getManyToOneOuterKeyName(): string
    {
        return $this->manyToOneOuterKeyName;
    }

    public function getManyToOneInnerKeyName(): string
    {
        return $this->manyToOneInnerKeyName;
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
     * @return FetcherInterface<TInner>
     */
    public function getFetcher(): FetcherInterface
    {
        return $this->fetcher;
    }

    public function getResult(array $outerKeys, JoinStrategyInterface $joinStrategy): iterable
    {
        $queryBuilder = $this->queryBuilder;
        $grammar = $queryBuilder->getGrammar();

        if (count($queryBuilder->getSelectBuilder()) === 0) {
            $queryBuilder = $queryBuilder
                ->select($grammar->identifier($this->manyToOneTableName) . '.*');
        }

        $queryBuilder = $queryBuilder
            ->select(
                $grammar->identifier($this->oneToManyTableName) . '.' . $grammar->identifier($this->oneToManyInnerKeyName),
                $grammar->identifier($this->getPivotKey())
            );

        if (count($queryBuilder->getFrom()) === 0) {
            $queryBuilder = $queryBuilder->from($grammar->identifier($this->manyToOneTableName));
        }

        return $queryBuilder
            ->outerJoin(
                $grammar->identifier($this->oneToManyTableName),
                sprintf(
                    '%s.%s = %s.%s',
                    $grammar->identifier($this->manyToOneTableName),
                    $grammar->identifier($this->manyToOneInnerKeyName),
                    $grammar->identifier($this->oneToManyTableName),
                    $grammar->identifier($this->manyToOneOuterKeyName)
                ),
                null,
                0
            )
            ->where(
                $grammar->identifier($this->oneToManyTableName) . '.' . $grammar->identifier($this->oneToManyInnerKeyName),
                'IN',
                $outerKeys
            )
            ->getResult($this->fetcher);
    }
}
