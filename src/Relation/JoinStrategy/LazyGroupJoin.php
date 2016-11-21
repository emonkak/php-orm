<?php

namespace Emonkak\Orm\Relation\JoinStrategy;

use Emonkak\Enumerable\Iterator\MemoizeIterator;
use Emonkak\Enumerable\Iterator\SelectIterator;
use Emonkak\Orm\ResultSet\ResultSetInterface;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use ProxyManager\Proxy\LazyLoadingInterface;

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
    public function join(ResultSetInterface $outer, ResultSetInterface $inner, callable $outerKeySelector, callable $innerKeySelector, callable $resultSelector)
    {
        $proxyFactory = $this->proxyFactory;
        $cachedElements = null;

        return new SelectIterator($outer, static function($outerElement) use ($proxyFactory, &$cachedElements, $inner, $outerKeySelector, $innerKeySelector, $resultSelector) {
            $initializer = static function (&$wrappedObject, LazyLoadingInterface $proxy, $method, array $parameters, &$initializer) use (&$cachedElements, $outerElement, $inner, $outerKeySelector, $innerKeySelector) {
                $initializer = null;

                if ($cachedElements === null) {
                    $cachedElements = [];
                    foreach ($inner as $innerElement) {
                        $innerKey = $innerKeySelector($innerElement);
                        if (!isset($cachedElements[$innerKey])) {
                            $cachedElements[$innerKey] = [];
                        }
                        $cachedElements[$innerKey][] = $innerElement;
                    }
                }

                $outerKey = $outerKeySelector($outerElement);
                $joinedElements = isset($cachedElements[$outerKey]) ? $cachedElements[$outerKey] : [];
                $wrappedObject = new \ArrayObject($joinedElements);

                return true;
            };
            $proxy = $proxyFactory->createProxy(\ArrayObject::class, $initializer);
            return $resultSelector($outerElement, $proxy);
        });
    }
}
