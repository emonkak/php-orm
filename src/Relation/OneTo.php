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
class OneTo implements RelationStrategyInterface
{
    private string $relationKeyName;

    private string $tableName;

    private string $outerKeyName;

    private string $innerKeyName;

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
        string $tableName,
        string $outerKeyName,
        string $innerKeyName,
        SelectBuilder $queryBuilder,
        FetcherInterface $fetcher
    ) {
        $this->relationKeyName = $relationKeyName;
        $this->tableName = $tableName;
        $this->outerKeyName = $outerKeyName;
        $this->innerKeyName = $innerKeyName;
        $this->queryBuilder = $queryBuilder;
        $this->fetcher = $fetcher;
    }

    public function getRelationKeyName(): string
    {
        return $this->relationKeyName;
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function getOuterKeyName(): string
    {
        return $this->outerKeyName;
    }

    public function getInnerKeyName(): string
    {
        return $this->innerKeyName;
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

        if (count($queryBuilder->getFrom()) === 0) {
            $queryBuilder = $queryBuilder->from($grammar->identifier($this->tableName));
        }

        return $queryBuilder
            ->where(
                $grammar->identifier($this->tableName) . '.' . $grammar->identifier($this->innerKeyName),
                'IN',
                $outerKeys
            )
            ->getResult($this->fetcher);
    }
}
