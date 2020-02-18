<?php

declare(strict_types=1);

namespace Emonkak\Orm\Fetcher;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\QueryBuilderInterface;
use Emonkak\Orm\Relation\RelationInterface;
use Emonkak\Orm\ResultSet\RelationResultSet;
use Emonkak\Orm\ResultSet\ResultSetInterface;

/**
 * @template TOuter
 * @template TResult
 * @implements FetcherInterface<TResult>
 */
class RelationFetcher implements FetcherInterface
{
    /**
     * @use Relatable<TResult>
     */
    use Relatable;

    /**
     * @psalm-var FetcherInterface<TOuter>
     * @var FetcherInterface
     */
    private $fetcher;

    /**
     * @psalm-var RelationInterface<TOuter,TResult>
     * @var RelationInterface
     */
    private $relation;

    /**
     * @psalm-param FetcherInterface<TOuter> $fetcher
     * @psalm-param RelationInterface<TOuter,TResult> $relation
     */
    public function __construct(FetcherInterface $fetcher, RelationInterface $relation)
    {
        $this->fetcher = $fetcher;
        $this->relation = $relation;
    }

    /**
     * @psalm-return FetcherInterface<TOuter>
     */
    public function getFetcher(): FetcherInterface
    {
        return $this->fetcher;
    }

    /**
     * @psalm-return RelationInterface<TOuter,TResult>
     */
    public function getRelation(): RelationInterface
    {
        return $this->relation;
    }

    public function getPdo(): PDOInterface
    {
        return $this->fetcher->getPdo();
    }

    /**
     * {@inheritDoc}
     */
    public function getClass(): ?string
    {
        return $this->relation->getResultClass();
    }

    /**
     * {@inheritDoc}
     */
    public function fetch(QueryBuilderInterface $queryBuilder): ResultSetInterface
    {
        $result = $this->fetcher->fetch($queryBuilder);
        $class = $this->fetcher->getClass();
        return new RelationResultSet($result, $class, $this->relation);
    }
}
