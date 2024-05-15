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
     * @var FetcherInterface<TOuter>
     */
    private FetcherInterface $fetcher;

    /**
     * @var RelationInterface<TOuter,TResult>
     */
    private RelationInterface $relation;

    /**
     * @param FetcherInterface<TOuter> $fetcher
     * @param RelationInterface<TOuter,TResult> $relation
     */
    public function __construct(FetcherInterface $fetcher, RelationInterface $relation)
    {
        $this->fetcher = $fetcher;
        $this->relation = $relation;
    }

    /**
     * @return FetcherInterface<TOuter>
     */
    public function getFetcher(): FetcherInterface
    {
        return $this->fetcher;
    }

    /**
     * @return RelationInterface<TOuter,TResult>
     */
    public function getRelation(): RelationInterface
    {
        return $this->relation;
    }

    public function getPdo(): PDOInterface
    {
        return $this->fetcher->getPdo();
    }

    public function getClass(): ?string
    {
        return $this->relation->getResultClass();
    }

    public function fetch(QueryBuilderInterface $queryBuilder): ResultSetInterface
    {
        $result = $this->fetcher->fetch($queryBuilder);
        $class = $this->fetcher->getClass();
        return new RelationResultSet($result, $class, $this->relation);
    }
}
