<?php

declare(strict_types=1);

namespace Emonkak\Orm\Fetcher;

use Emonkak\Orm\Relation\RelationInterface;

/**
 * @template TOuter
 */
trait Relatable
{
    /**
     * @return ?class-string
     */
    abstract public function getClass(): ?string;

    /**
     * @template TResult
     * @param callable(?class-string):RelationInterface<TOuter,TResult> $relationFactory
     * @return RelationFetcher<TOuter,TResult>
     */
    public function with(callable $relationFactory): RelationFetcher
    {
        /** @var FetcherInterface<TOuter> $this */
        $class = $this->getClass();
        $relation = $relationFactory($class);
        return new RelationFetcher($this, $relation);
    }

    /**
     * @template TResult
     * @param RelationInterface<TOuter,TResult> $relation
     * @return RelationFetcher<TOuter,TResult>
     */
    public function withRelation(RelationInterface $relation): RelationFetcher
    {
        /** @var FetcherInterface<TOuter> $this */
        return new RelationFetcher($this, $relation);
    }
}
