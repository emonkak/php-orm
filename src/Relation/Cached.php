<?php

namespace Emonkak\Orm\Relation;

use Emonkak\Orm\ResultSet\PreloadedResultSet;
use Emonkak\Orm\ResultSet\ResultSetInterface;
use Psr\SimpleCache\CacheInterface;

class Cached implements RelationStrategyInterface
{
    /**
     * @var RelationStrategyInterface
     */
    private $relationStrategy;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var callable
     */
    private $cacheKeySelector;

    /**
     * @var ?int
     */
    private $cacheTtl;

    /**
     * @param RelationStrategyInterface $relationStrategy
     * @param CacheInterface            $cache
     * @param callable                  $cacheKeySelector
     * @param ?int                      $cacheTtl
     */
    public function __construct(
        RelationStrategyInterface $relationStrategy,
        CacheInterface $cache,
        callable $cacheKeySelector,
        $cacheTtl
    ) {
        $this->relationStrategy = $relationStrategy;
        $this->cache = $cache;
        $this->cacheKeySelector = $cacheKeySelector;
        $this->cacheTtl = $cacheTtl;
    }

    /**
     * @return RelationStrategyInterface
     */
    public function getInnerRelationStrategy()
    {
        return $this->relationStrategy;
    }

    /**
     * @return CacheInterface
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @return callable
     */
    public function getCacheKeySelector()
    {
        return $this->cacheKeySelector;
    }

    /**
     * @return ?int
     */
    public function getCacheTtl()
    {
        return $this->cacheTtl;
    }

    /**
     * {@inheritDoc}
     */
    public function getResult(array $outerKeys)
    {
        $cacheKeyIndexes = [];
        $cacheKeySelector = $this->cacheKeySelector;

        foreach ($outerKeys as $outerKey) {
            $cacheKeyIndexes[$cacheKeySelector($outerKey)] = $outerKey;
        }

        $cacheItems = $this->cache->getMultiple(array_keys($cacheKeyIndexes));
        $cachedElements = [];
        $uncachedOuterKeys = [];

        foreach ($cacheItems as $key => $value) {
            if ($value !== null) {
                $cachedElements[] = $value;
            } else {
                $uncachedOuterKeys[] = $cacheKeyIndexes[$key];
            }
        }

        if (!empty($uncachedOuterKeys)) {
            $result = $this->relationStrategy->getResult($uncachedOuterKeys);
            $innerClass = $result->getClass();
            $innerKeySelector = $this->relationStrategy->getInnerKeySelector($innerClass);
            $freshCacheItems = [];

            foreach ($result as $element) {
                $cacheKey = $cacheKeySelector($innerKeySelector($element));

                $freshCacheItems[$cacheKey] = $element;

                $cachedElements[] = $element;
            }

            $this->cache->setMultiple($freshCacheItems, $this->cacheTtl);
        } else {
            $innerClass = get_class($cachedElements[0]);
        }

        return new PreloadedResultSet($cachedElements, $innerClass);
    }

    /**
     * {@inheritDoc}
     */
    public function getOuterKeySelector($outerClass)
    {
        return $this->relationStrategy->getOuterKeySelector($outerClass);
    }

    /**
     * {@inheritDoc}
     */
    public function getInnerKeySelector($innerClass)
    {
        return $this->relationStrategy->getInnerKeySelector($innerClass);
    }

    /**
     * {@inheritDoc}
     */
    public function getResultSelector($outerClass, $innerClass)
    {
        return $this->relationStrategy->getResultSelector($outerClass, $innerClass);
    }
}
