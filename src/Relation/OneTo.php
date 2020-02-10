<?php

declare(strict_types=1);

namespace Emonkak\Orm\Relation;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\ResultSet\ResultSetInterface;
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
     * @param array<string,SelectBuilder> $unions
     */
    public function __construct(
        string $relationKey,
        string $table,
        string $outerKey,
        string $innerKey,
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
        $queryBuilder = $this->createQueryBuilderFrom($this->queryBuilder, $this->table, $outerKeys);

        foreach ($this->unions as $unionTable => $unionBuilder) {
            $unionBuilder = $this->createQueryBuilderFrom($unionBuilder, $unionTable, $outerKeys);

            $queryBuilder = $queryBuilder->unionAllWith($unionBuilder);
        }

        return $queryBuilder
            ->getResult($this->pdo, $this->fetcher);
    }

    public function getOuterKeySelector(?string $outerClass): callable
    {
        return AccessorCreators::createKeySelector($this->outerKey, $outerClass);
    }

    public function getInnerKeySelector(?string $innerClass): callable
    {
        return AccessorCreators::createKeySelector($this->innerKey, $innerClass);
    }

    public function getResultSelector(?string $outerClass, ?string $innerClass): callable
    {
        return AccessorCreators::createKeyAssignee($this->relationKey, $outerClass);
    }

    /**
     * @param mixed[] $outerKeys
     */
    private function createQueryBuilderFrom(SelectBuilder $queryBuilder, string $table, array $outerKeys): SelectBuilder
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
