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
     * @return ?class-string<TOuter>
     */
    abstract public function getClass(): ?string;

    /**
     * @template TResult
     * @param callable(?class-string<TOuter>):RelationInterface<TOuter,TResult> $relationFactory
     * @return RelationFetcher<TOuter,TResult>
     */
    public function with(callable $relationFactory): RelationFetcher
    {
        $class = $this->getClass();
        $relation = $relationFactory($class);
        return new RelationFetcher($this, $relation);
    }
}
