<?php

namespace Emonkak\Orm\Relation;

use Emonkak\Orm\ResultSet\PreloadResultSet;
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
     * @var string
     */
    private $cachePrefix;

    /**
     * @var integer|null
     */
    private $cacheTtl;

    /**
     * @param RelationStrategyInterface $relationStrategy
     * @param CacheInterface            $cache
     * @param string                    $cachePrefix
     * @param integer|null              $cacheTtl
     */
    public function __construct(
        RelationStrategyInterface $relationStrategy,
        CacheInterface $cache,
        $cachePrefix,
        $cacheTtl
    ) {
        $this->relationStrategy = $relationStrategy;
        $this->cache = $cache;
        $this->cachePrefix = $cachePrefix;
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
     * @return string
     */
    public function getCachePrefix()
    {
        return $this->cachePrefix;
    }

    /**
     * @return integer|null
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
        $cacheKeys = [];
        $cachePrefixLength = strlen($this->cachePrefix);

        foreach ($outerKeys as $outerKey) {
            $cacheKeys[] = $this->cachePrefix . $outerKey;
        }

        $cacheItems = $this->cache->getMultiple(array_unique($cacheKeys));
        $cachedElements = [];
        $uncachedOuterKeys = [];

        foreach ($cacheItems as $key => $value) {
            if ($value !== null) {
                $cachedElements[] = $value;
            } else {
                $uncachedOuterKeys[] = substr($key, $cachePrefixLength);
            }
        }

        if (!empty($uncachedOuterKeys)) {
            $result = $this->relationStrategy->getResult($uncachedOuterKeys);
            $innerClass = $result->getClass();
            $innerKeySelector = $this->relationStrategy->getInnerKeySelector($innerClass);
            $freshCacheItems = [];

            foreach ($result as $element) {
                $innerKey = $innerKeySelector($element);
                $cacheKey = $this->cachePrefix . $innerKey;

                $freshCacheItems[$cacheKey] = $element;

                $cachedElements[] = $element;
            }

            $this->cache->setMultiple($freshCacheItems, $this->cacheTtl);
        } else {
            $innerClass = get_class($cachedElements[0]);
        }

        return new PreloadResultSet($cachedElements, $innerClass);
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
