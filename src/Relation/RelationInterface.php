<?php

declare(strict_types=1);

namespace Emonkak\Orm\Relation;

/**
 * @template TOuter
 * @template TResult
 */
interface RelationInterface
{
    /**
     * @return ?class-string
     */
    public function getResultClass(): ?string;

    /**
     * Associates between the outer result and the relation result.
     *
     * @param iterable<TOuter> $outerResult
     * @param ?class-string $outerClass
     * @return \Traversable<TResult>
     */
    public function associate(iterable $outerResult, ?string $outerClass): \Traversable;
}
