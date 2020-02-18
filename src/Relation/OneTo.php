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
     * @var SelectBuilder
     */
    private $queryBuilder;

    /**
     * @psalm-var FetcherInterface<TInner>
     * @var FetcherInterface
     */
    private $fetcher;

    /**
     * @psalm-param FetcherInterface<TInner> $fetcher
     */
    public function __construct(
        string $relationKey,
        string $table,
        string $outerKey,
        string $innerKey,
        SelectBuilder $queryBuilder,
        FetcherInterface $fetcher
    ) {
        $this->relationKey = $relationKey;
        $this->table = $table;
        $this->outerKey = $outerKey;
        $this->innerKey = $innerKey;
        $this->queryBuilder = $queryBuilder;
        $this->fetcher = $fetcher;
    }

    public function getRelationKey(): string
    {
        return $this->relationKey;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function getOuterKey(): string
    {
        return $this->outerKey;
    }

    public function getInnerKey(): string
    {
        return $this->innerKey;
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
     * {@inheritDoc}
     */
    public function getResult(array $outerKeys, JoinStrategyInterface $joinStrategy): iterable
    {
        $queryBuilder = $this->queryBuilder;
        $grammar = $queryBuilder->getGrammar();

        if (count($queryBuilder->getFrom()) === 0) {
            $queryBuilder = $queryBuilder->from($grammar->identifier($this->table));
        }

        return $queryBuilder
            ->where(
                $grammar->identifier($this->table) . '.' . $grammar->identifier($this->innerKey),
                'IN',
                $outerKeys
            )
            ->getResult($this->fetcher);
    }
}
