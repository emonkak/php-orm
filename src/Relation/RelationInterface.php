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
     * @psalm-return ?class-string<TResult>
     */
    public function getResultClass(): ?string;

    /**
     * Associates between the outer result and the relation result.
     *
     * @psalm-param iterable<TOuter> $outerResult
     * @psalm-param ?class-string<TOuter> $outerClass
     * @psalm-return \Traversable<TResult>
     */
    public function associate(iterable $outerResult, ?string $outerClass): \Traversable;
}
