<?php

namespace Emonkak\Orm\Relation\JoinStrategy;

use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use ProxyManager\Proxy\LazyLoadingInterface;
use Emonkak\Enumerable\Iterator\MemoizeIterator;
use Emonkak\Enumerable\Iterator\SelectIterator;
use Emonkak\Enumerable\Iterator\WhereIterator;

/**
 * @internal
 */
class LazyOuterJoinStrategy
{
    /**
     * {@inheritDoc}
     */
    public function __invoke($outer, $inner, callable $outerKeySelector, callable $innerKeySelector, callable $resultSelector)
    {
        $factory = new LazyLoadingValueHolderFactory();
        $innerElements = new MemoizeIterator($inner);
        $innerClass = (new \ReflectionFunction($innerKeySelector))->getClosureScopeClass()->getName();

        return new SelectIterator($outer, static function($outerElement) use ($factory, $innerElements, $innerClass, $outerKeySelector, $innerKeySelector, $resultSelector) {
            $outerKey = $outerKeySelector($outerElement);
            $initializer = function (&$wrappedObject, LazyLoadingInterface $proxy, $method, array $parameters, &$initializer) use ($outerElement, $innerElements, $outerKey, $innerKeySelector) {
                $initializer = null;

                foreach ($innerElements as $innerElement) {
                    if ($innerKeySelector($innerElement) === $outerKey) {
                        $wrappedObject = $innerElement;
                        return true;
                    }
                }

                return false;
            };
            $proxy = $factory->createProxy($innerClass, $initializer);
            return $resultSelector($outerElement, $proxy);
        });
    }
}
