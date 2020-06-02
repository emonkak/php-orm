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
     * @psalm-return ?class-string<TOuter>
     */
    abstract public function getClass(): ?string;

    /**
     * @template TResult
     * @psalm-param callable(?class-string<TOuter>):RelationInterface<TOuter,TResult> $relationFactory
     * @psalm-return RelationFetcher<TOuter,TResult>
     */
    public function with(callable $relationFactory): RelationFetcher
    {
        $class = $this->getClass();
        $relation = $relationFactory($class);
        return new RelationFetcher($this, $relation);
    }

    /**
     * @template TResult
     * @psalm-param RelationInterface<TOuter,TResult> $relation
     * @psalm-return RelationFetcher<TOuter,TResult>
     */
    public function withRelation(RelationInterface $relation): RelationFetcher
    {
        return new RelationFetcher($this, $relation);
    }
}
