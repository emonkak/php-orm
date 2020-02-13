<?php

declare(strict_types=1);

namespace Emonkak\Orm\Relation\JoinStrategy;

use Emonkak\Enumerable\EqualityComparerInterface;
use Emonkak\Enumerable\Iterator\SelectIterator;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;

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
     * @psalm-var callable(TOuter,LazyValue<?TInner>):TResult $resultSelector
     * @var callable
     */
    private $resultSelector;

    /**
     * @psalm-var EqualityComparerInterface<TKey>
     * @var EqualityComparerInterface
     */
    private $comparer;

    /**
     * @var LazyLoadingValueHolderFactory
     */
    private $proxyFactory;

    /**
     * @psalm-param callable(TOuter):TKey $outerKeySelector
     * @psalm-param callable(TInner):TKey $innerKeySelector
     * @psalm-param callable(TOuter,LazyValue<?TInner>):TResult $resultSelector
     * @psalm-param EqualityComparerInterface<TKey> $comparer
     */
    public function __construct(
        callable $outerKeySelector,
        callable $innerKeySelector,
        callable $resultSelector,
        EqualityComparerInterface $comparer,
        LazyLoadingValueHolderFactory $proxyFactory
    ) {
        $this->outerKeySelector = $outerKeySelector;
        $this->innerKeySelector = $innerKeySelector;
        $this->resultSelector = $resultSelector;
        $this->comparer = $comparer;
        $this->proxyFactory = $proxyFactory;
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
     * @psalm-return callable(TOuter,LazyValue<?TInner>):TResult
     */
    public function getResultSelector(): callable
    {
        return $this->resultSelector;
    }

    public function getComparer(): EqualityComparerInterface
    {
        return $this->comparer;
    }

    public function getProxyFactory(): LazyLoadingValueHolderFactory
    {
        return $this->proxyFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function join(iterable $outer, iterable $inner): \Traversable
    {
        $cachedElements = null;

        return new SelectIterator(
            $outer,
            /**
             * @psalm-param TOuter $outerElement
             * @psalm-return TResult
             */
            function($outerElement) use (
                &$cachedElements,
                $inner
            ) {
                $outerKeySelector = $this->outerKeySelector;
                $innerKeySelector = $this->innerKeySelector;
                $resultSelector = $this->resultSelector;
                $comparer = $this->comparer;

                /** @psalm-var LazyValue<?TInner> */
                $innerProxy = $this->proxyFactory->createProxy(
                    LazyValue::class,
                    static function (?object &$wrappedObject, object $proxy, string $method, array $parameters, ?\Closure &$initializer) use (
                        &$cachedElements,
                        $outerElement,
                        $inner,
                        $outerKeySelector,
                        $innerKeySelector,
                        $comparer
                    ): bool {
                        $initializer = null;

                        if ($cachedElements === null) {
                            $cachedElements = [];
                            foreach ($inner as $innerElement) {
                                $innerKey = $innerKeySelector($innerElement);
                                $innerHash = $comparer->hash($innerKey);
                                $cachedElements[$innerHash] = $innerElement;
                            }
                        }

                        $outerKey = $outerKeySelector($outerElement);
                        $outerHash = $comparer->hash($outerKey);

                        if (isset($cachedElements[$outerHash])) {
                            $wrappedObject = new LazyValue($cachedElements[$outerHash]);
                        } else {
                            $wrappedObject = new LazyValue(null);
                        }

                        return true;
                    }
                );

                return $resultSelector($outerElement, $innerProxy);
            }
        );
    }
}
