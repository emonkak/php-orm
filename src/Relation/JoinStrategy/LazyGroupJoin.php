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
class LazyGroupJoin implements JoinStrategyInterface
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
     * @var callable(TOuter,LazyCollection<TInner,TKey>):TResult
     */
    private $resultSelector;

    /**
     * @var EqualityComparerInterface<TKey>
     */
    private EqualityComparerInterface $comparer;

    /**
     * @param callable(TOuter):TKey $outerKeySelector
     * @param callable(TInner):TKey $innerKeySelector
     * @param callable(TOuter,LazyCollection<TInner,TKey>):TResult $resultSelector
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
     * @return callable(TOuter,LazyCollection<TInner,TKey>):TResult
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
        /** @var ?TInner[] */
        $cachedInner = null;

        $innerKeySelector = $this->innerKeySelector;
        $outerKeySelector = $this->outerKeySelector;
        $resultSelector = $this->resultSelector;
        $comparer = $this->comparer;

        $fetchCachedItems =
            /**
             * @return array<string,TInner[]>
             */
            static function() use (
                $inner,
                $innerKeySelector,
                $comparer
            ): array {
                $cachedInner = [];

                foreach ($inner as $innerElement) {
                    $innerKey = $innerKeySelector($innerElement);
                    $innerHash = $comparer->hash($innerKey);
                    $cachedInner[$innerHash][] = $innerElement;
                }

                return $cachedInner;
            };

        $evaluator =
            /**
             * @param TKey $outerKey
             * @return TInner[]
             */
            static function(mixed $outerKey) use (
                &$cachedInner,
                &$fetchCachedItems,
                $comparer
            ): mixed {
                if ($cachedInner === null) {
                    /** @var callable():array<string,TInner[]> $fetchCachedItems */
                    $cachedInner = $fetchCachedItems();
                    $fetchCachedItems = null;
                }

                $outerHash = $comparer->hash($outerKey);

                return $cachedInner[$outerHash] ?? [];
            };

        return new SelectIterator(
            $outer,
            /**
             * @param TOuter $outerElement
             * @return TResult
             */
            static function(mixed $outerElement) use (
                $outerKeySelector,
                $resultSelector,
                $evaluator
            ): mixed {
                $outerKey = $outerKeySelector($outerElement);
                $innerProxy = new LazyCollection($outerKey, $evaluator);
                return $resultSelector($outerElement, $innerProxy);
            }
        );
    }
}
