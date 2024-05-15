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
     * @return callable(TOuter):TKey
     */
    public function getOuterKeySelector(): callable;

    /**
     * @return callable(TInner):TKey
     */
    public function getInnerKeySelector(): callable;

    /**
     * @param iterable<TOuter> $outer
     * @param iterable<TInner> $inner
     * @return \Traversable<TResult>
     */
    public function join(iterable $outer, iterable $inner): \Traversable;
}
