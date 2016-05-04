<?php

namespace Emonkak\Orm\Relation\JoinStrategy;

use Emonkak\Enumerable\Iterator\MemoizeIterator;
use Emonkak\Enumerable\Iterator\SelectIterator;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use ProxyManager\Proxy\LazyLoadingInterface;

/**
 * @internal
 */
class LazyGroupJoinStrategy
{
    /**
     * {@inheritDoc}
     */
    public function __invoke($outer, $inner, callable $outerKeySelector, callable $innerKeySelector, callable $resultSelector)
    {
        $factory = new LazyLoadingValueHolderFactory();
        $innerElements = new MemoizeIterator($inner);

        return new SelectIterator($outer, static function($outerElement) use ($factory, $innerElements, $outerKeySelector, $innerKeySelector, $resultSelector) {
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
            $proxy = $factory->createProxy(\ArrayObject::class, $initializer);
            return $resultSelector($outerElement, $proxy);
        });
    }
}
