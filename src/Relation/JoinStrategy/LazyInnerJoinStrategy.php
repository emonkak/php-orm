<?php

namespace Emonkak\Orm\Relation\JoinStrategy;

use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use ProxyManager\Proxy\LazyLoadingInterface;
use Emonkak\Enumerable\Iterator\SelectIterator;
use Emonkak\Enumerable\Iterator\WhereIterator;

/**
 * @internal
 */
class LazyInnerJoinStrategy implements JoinStrategyInterface
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
        $innerClass = (new \ReflectionFunction($innerKeySelector))->getClosureScopeClass()->getName();
        $innerElements = [];
        $cached = false;

        return new SelectIterator($outer, static function($outerElement) use ($proxyFactory, $inner, $innerClass, $outerKeySelector, $innerKeySelector, $resultSelector, &$innerElements, &$cached) {
            $outerKey = $outerKeySelector($outerElement);
            $initializer = function (&$wrappedObject, LazyLoadingInterface $proxy, $method, array $parameters, &$initializer) use ($outerElement, $inner, $outerKey, $innerKeySelector, &$innerElements, &$cached) {
                $initializer = null;

                if (!$cached) {
                    foreach ($inner as $innerElement) {
                        $innerElements[$innerKeySelector($innerElement)] = $innerElement;
                    }
                    $cached = true;
                }

                if (isset($innerElements[$outerKey])) {
                    $wrappedObject = $innerElements[$outerKey];
                    return true;
                }

                return false;
            };
            $proxy = $proxyFactory->createProxy($innerClass, $initializer);
            return $resultSelector($outerElement, $proxy);
        });
    }
}
