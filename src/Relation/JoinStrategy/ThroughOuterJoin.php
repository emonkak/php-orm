<?php

declare(strict_types=1);

namespace Emonkak\Orm\Relation\JoinStrategy;

use Emonkak\Enumerable\EqualityComparerInterface;
use Emonkak\Enumerable\Iterator\OuterJoinIterator;

/**
 * @template TOuter
 * @template TInner
 * @template TKey
 * @template TThroughKey
 * @template TResult
 * @implements JoinStrategyInterface<TOuter,TInner,TKey,TResult>
 */
class ThroughOuterJoin implements JoinStrategyInterface
{
    /**
     * @psalm-var callable(TOuter):TKey
     * @var callable
     */
    private $outerKeySelector;

    /**
     * @psalm-var callable(TInner):TKey
     * @var callable
     */
    private $innerKeySelector;

    /**
     * @psalm-var callable(TInner):TThroughKey
     * @var callable
     */
    private $throughKeySelector;

    /**
     * @psalm-var callable(TOuter,?TThroughKey):TResult $resultSelector
     * @var callable
     */
    private $resultSelector;

    /**
     * @psalm-var EqualityComparerInterface<TKey>
     * @var EqualityComparerInterface
     */
    private $comparer;

    /**
     * @psalm-param callable(TOuter):TKey $outerKeySelector
     * @psalm-param callable(TInner):TKey $innerKeySelector
     * @psalm-param callable(TInner):TThroughKey $throughKeySelector
     * @psalm-param callable(TOuter,?TThroughKey):TResult $resultSelector
     * @psalm-param EqualityComparerInterface<TKey> $comparer
     */
    public function __construct(
        callable $outerKeySelector,
        callable $innerKeySelector,
        callable $throughKeySelector,
        callable $resultSelector,
        EqualityComparerInterface $comparer
    ) {
        $this->outerKeySelector = $outerKeySelector;
        $this->innerKeySelector = $innerKeySelector;
        $this->throughKeySelector = $throughKeySelector;
        $this->resultSelector = $resultSelector;
        $this->comparer = $comparer;
    }

    /**
     * {@inheritDoc}
     */
    public function getOuterKeySelector(): callable
    {
        return $this->outerKeySelector;
    }

    /**
     * {@inheritDoc}
     */
    public function getInnerKeySelector(): callable
    {
        return $this->innerKeySelector;
    }

    /**
     * @psalm-return callable(TInner):TThroughKey
     */
    public function getThroughKeySelector(): callable
    {
        return $this->throughKeySelector;
    }

    /**
     * @psalm-return callable(TOuter,?TInner):TResult
     */
    public function getResultSelector(): callable
    {
        return $this->resultSelector;
    }

    /**
     * @psalm-return EqualityComparerInterface<TKey>
     */
    public function getComparer(): EqualityComparerInterface
    {
        return $this->comparer;
    }

    /**
     * {@inheritDoc}
     */
    public function join(iterable $outer, iterable $inner): \Traversable
    {
        $throughKeySelector = $this->throughKeySelector;
        $resultSelector = $this->resultSelector;

        return new OuterJoinIterator(
            $outer,
            $inner,
            $this->outerKeySelector,
            $this->innerKeySelector,
            /**
             * @psalm-param TOuter $lhs
             * @psalm-param ?TInner $rhs
             * @psalm-return TResult
             */
            static function($lhs, $rhs) use ($throughKeySelector, $resultSelector) {
                $throughKey = $rhs !== null ? $throughKeySelector($rhs) : null;
                return $resultSelector($lhs, $throughKey);
            },
            $this->comparer
        );
    }
}
