<?php

namespace Emonkak\Orm\Relation\JoinStrategy;

use Emonkak\Enumerable\Iterator\SelectIterator;
use Emonkak\Orm\ResultSet\ResultSetInterface;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use ProxyManager\Proxy\LazyLoadingInterface;

class LazyOuterJoin implements JoinStrategyInterface
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
                        $cachedElements[$innerKeySelector($innerElement)] = $innerElement;
                    }
                }

                $outerKey = $outerKeySelector($outerElement);
                if (isset($cachedElements[$outerKey])) {
                    $wrappedObject = $cachedElements[$outerKey];
                } else {
                    // Wraps the dummy object instead of null.
                    $wrappedObject = (new \ReflectionClass($inner->getClass()))  // @phan-suppress-current-line PhanTypeMismatchArgumentNullableInternal
                        ->newInstanceWithoutConstructor();
                }

                return true;
            };
            $innerClass = $inner->getClass();
            $proxy = $proxyFactory->createProxy($innerClass, $initializer);  // @phan-suppress-current-line PhanTypeMismatchArgumentNullable
            return $resultSelector($outerElement, $proxy);
        });
    }
}
