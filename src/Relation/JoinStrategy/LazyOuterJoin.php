<?php

declare(strict_types=1);

namespace Emonkak\Orm\Relation\JoinStrategy;

use Emonkak\Enumerable\EqualityComparerInterface;
use Emonkak\Enumerable\Iterator\SelectIterator;

/**
 * @template TOuter
 * @template TInner
 * @template TKey
 * @template TResult
 * @implements JoinStrategyInterface<TOuter,TInner,TKey,TResult>
 */
class LazyOuterJoin implements JoinStrategyInterface
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
     * @psalm-var callable(TOuter,LazyValue<?TInner,TKey>):TResult $resultSelector
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
     * @psalm-param callable(TOuter,LazyValue<?TInner,TKey>):TResult $resultSelector
     * @psalm-param EqualityComparerInterface<TKey> $comparer
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
     * @psalm-return callable(TOuter):TKey
     */
    public function getOuterKeySelector(): callable
    {
        return $this->outerKeySelector;
    }

    /**
     * @psalm-return callable(TInner):TKey
     */
    public function getInnerKeySelector(): callable
    {
        return $this->innerKeySelector;
    }

    /**
     * @psalm-return callable(TOuter,LazyValue<?TInner,TKey>):TResult
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
     * @psalm-param iterable<TOuter> $outer
     * @psalm-param iterable<TInner> $inner
     * @psalm-return \Traversable<TResult>
     */
    public function join(iterable $outer, iterable $inner): \Traversable
    {
        /** @psalm-var ?TInner[] */
        $cachedInner = null;

        $innerKeySelector = $this->innerKeySelector;
        $outerKeySelector = $this->outerKeySelector;
        $resultSelector = $this->resultSelector;
        $comparer = $this->comparer;

        $fetchCachedItems =
            /**
             * @psalm-return array<string,TInner>
             */
            static function() use (
                $inner,
                $innerKeySelector,
                $comparer
            ) {
                $cachedInner = [];

                foreach ($inner as $innerElement) {
                    $innerKey = $innerKeySelector($innerElement);
                    $innerHash = $comparer->hash($innerKey);
                    $cachedInner[$innerHash] = $innerElement;
                }

                return $cachedInner;
            };

        $evaluator =
            /**
             * @psalm-param TKey $outerKey
             * @psalm-return ?TInner
             */
            static function($outerKey) use (
                &$cachedInner,
                &$fetchCachedItems,
                $comparer
            ) {
                if ($cachedInner === null && $fetchCachedItems !== null) {
                    /** @var callable():array<string,TInner> $fetchCachedItems */
                    $cachedInner = $fetchCachedItems();
                    $fetchCachedItems = null;
                }

                $outerHash = $comparer->hash($outerKey);

                return $cachedInner[$outerHash] ?? null;
            };

        /** @psalm-var \Traversable<TResult> */
        return new SelectIterator(
            $outer,
            /**
             * @psalm-param TOuter $outerElement
             * @psalm-return TResult
             */
            static function($outerElement) use (
                $outerKeySelector,
                $resultSelector,
                $evaluator
            ) {
                $outerKey = $outerKeySelector($outerElement);
                $innerProxy = new LazyValue($outerKey, $evaluator);
                return $resultSelector($outerElement, $innerProxy);
            }
        );
    }
}
