<?php

namespace Emonkak\Orm\Relation\JoinStrategy;

use Emonkak\Enumerable\Iterator\MemoizeIterator;
use Emonkak\Enumerable\Iterator\SelectIterator;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use ProxyManager\Proxy\LazyLoadingInterface;

/**
 * @internal
 */
class LazyGroupJoin implements JoinStrategyInterface
{
    /**
     * @var LazyLoadingValueHolderFactory
     */
    private $proxyFactory;

    /**
     * @param LazyLoadingValueHolderFactory $proxyFactory
     */
    public function __construct(LazyLoadingValueHolderFactory $proxyFactory)
    {
        $this->proxyFactory = $proxyFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke($outer, $inner, callable $outerKeySelector, callable $innerKeySelector, callable $resultSelector)
    {
        $proxyFactory = $this->proxyFactory;
        $innerElements = new MemoizeIterator($inner);

        return new SelectIterator($outer, static function($outerElement) use ($proxyFactory, $innerElements, $outerKeySelector, $innerKeySelector, $resultSelector) {
            $outerKey = $outerKeySelector($outerElement);
            $initializer = function (&$wrappedObject, LazyLoadingInterface $proxy, $method, array $parameters, &$initializer) use ($outerElement, $innerElements, $outerKey, $innerKeySelector) {
                $initializer = null;
                $joinedElements = [];

                foreach ($innerElements as $innerElement) {
                    if ($innerKeySelector($innerElement) === $outerKey) {
                        $joinedElements[] = $innerElement;
                    }
                }

                $wrappedObject = new \ArrayObject($joinedElements);
                return true;
            };
            $proxy = $proxyFactory->createProxy(\ArrayObject::class, $initializer);
            return $resultSelector($outerElement, $proxy);
        });
    }
}
