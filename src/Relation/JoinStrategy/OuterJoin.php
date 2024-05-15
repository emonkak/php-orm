<?php

declare(strict_types=1);

namespace Emonkak\Orm\Relation\JoinStrategy;

use Emonkak\Enumerable\EqualityComparerInterface;
use Emonkak\Enumerable\Iterator\OuterJoinIterator;

/**
 * @template TOuter
 * @template TInner
 * @template TKey
 * @template TResult
 * @implements JoinStrategyInterface<TOuter,TInner,TKey,TResult>
 */
class OuterJoin implements JoinStrategyInterface
{
    /**
     * @var callable(TOuter):TKey
     */
    private $outerKeySelector;

    /**
     * @var callable(TInner):TKey
     */
    private $innerKeySelector;

    /**
     * @var callable(TOuter,?TInner):TResult
     */
    private $resultSelector;

    /**
     * @var EqualityComparerInterface<TKey>
     */
    private EqualityComparerInterface $comparer;

    /**
     * @param callable(TOuter):TKey $outerKeySelector
     * @param callable(TInner):TKey $innerKeySelector
     * @param callable(TOuter,?TInner):TResult $resultSelector
     * @param EqualityComparerInterface<TKey> $comparer
     */
    public function __construct(
        callable $outerKeySelector,
        callable $innerKeySelector,
        callable $resultSelector,
        EqualityComparerInterface $comparer
    ) {
        $this->outerKeySelector = $outerKeySelector;
        $this->innerKeySelector = $innerKeySelector;
        $this->resultSelector = $resultSelector;
        $this->comparer = $comparer;
    }

    /**
     * @return callable(TOuter):TKey
     */
    public function getOuterKeySelector(): callable
    {
        return $this->outerKeySelector;
    }

    /**
     * @return callable(TInner):TKey
     */
    public function getInnerKeySelector(): callable
    {
        return $this->innerKeySelector;
    }

    /**
     * @return callable(TOuter,?TInner):TResult
     */
    public function getResultSelector(): callable
    {
        return $this->resultSelector;
    }

    /**
     * @return EqualityComparerInterface<TKey>
     */
    public function getComparer(): EqualityComparerInterface
    {
        return $this->comparer;
    }

    /**
     * @param iterable<TOuter> $outer
     * @param iterable<TInner> $inner
     * @return \Traversable<TResult>
     */
    public function join(iterable $outer, iterable $inner): \Traversable
    {
        return new OuterJoinIterator(
            $outer,
            $inner,
            $this->outerKeySelector,
            $this->innerKeySelector,
            $this->resultSelector,
            $this->comparer
        );
    }
}
