<?php

declare(strict_types=1);

namespace Emonkak\Orm\Relation\JoinStrategy;

/**
 * @template TOuter
 * @template TInner
 * @template TKey
 * @template TResult
 */
interface JoinStrategyInterface
{
    /**
     * @psalm-return callable(TOuter):TKey
     */
    public function getOuterKeySelector(): callable;

    /**
     * @psalm-return callable(TInner):TKey
     */
    public function getInnerKeySelector(): callable;

    /**
     * @psalm-param iterable<TOuter> $outer
     * @psalm-param iterable<TInner> $inner
     * @psalm-return \Traversable<TResult>
     */
    public function join(iterable $outer, iterable $inner): \Traversable;
}
