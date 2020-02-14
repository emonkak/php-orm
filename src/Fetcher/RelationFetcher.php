<?php

declare(strict_types=1);

namespace Emonkak\Orm\Fetcher;

use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\Relation\RelationInterface;
use Emonkak\Orm\ResultSet\RelationResultSet;
use Emonkak\Orm\ResultSet\ResultSetInterface;

/**
 * @template T
 * @template TResult
 * @implements FetcherInterface<TResult>
 * @use Relatable<T>
 */
class RelationFetcher implements FetcherInterface
{
    use Relatable;

    /**
     * @psalm-var FetcherInterface<T>
     * @var FetcherInterface
     */
    private $fetcher;

    /**
     * @psalm-var RelationInterface<T,TResult>
     * @var RelationInterface
     */
    private $relation;

    /**
     * @psalm-param FetcherInterface<T> $fetcher
     * @psalm-param RelationInterface<T,TResult> $relation
     */
    public function __construct(FetcherInterface $fetcher, RelationInterface $relation)
    {
        $this->fetcher = $fetcher;
        $this->relation = $relation;
    }

    /**
     * @psalm-return FetcherInterface<T>
     */
    public function getFetcher(): FetcherInterface
    {
        return $this->fetcher;
    }

    /**
     * @psalm-return RelationInterface<T,TResult>
     */
    public function getRelation(): RelationInterface
    {
        return $this->relation;
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
    public function fetch(PDOStatementInterface $stmt): ResultSetInterface
    {
        $result = $this->fetcher->fetch($stmt);
        $class = $this->fetcher->getClass();
        return new RelationResultSet($result, $class, $this->relation);
    }
}
