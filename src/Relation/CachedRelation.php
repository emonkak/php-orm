<?php

namespace Emonkak\Orm\Relation;

use Emonkak\Orm\ResultSet\PreloadResultSet;
use Emonkak\Orm\ResultSet\ResultSetInterface;
use Psr\SimpleCache\CacheInterface;

class CachedRelation extends AbstractStandardRelation
{
    /**
     * @var StandardRelationInterface
     */
    private $innerRelation;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var string
     */
    private $cachePrefix;

    /**
     * @var integer|\DateInterval|null
     */
    private $cacheTtl;

    /**
     * @param StandardRelationInterface  $innerRelation
     * @param CacheInterface             $cache
     * @param string                     $cachePrefix
     * @param integer|\DateInterval|null $cacheTtl
     */
    public function __construct(
        StandardRelationInterface $innerRelation,
        CacheInterface $cache,
        $cachePrefix,
        $cacheTtl
    ) {
        $this->innerRelation = $innerRelation;
        $this->cache = $cache;
        $this->cachePrefix = $cachePrefix;
        $this->cacheTtl = $cacheTtl;
    }

    /**
     * @return RelationInterface
     */
    public function getInnerRelation()
    {
        return $this->innerRelation;
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
     * @return integer|\DateInterval|null
     */
    public function getCacheTtl()
    {
        return $this->cacheTtl;
    }

    /**
     * {@inheritDoc}
     */
    public function getPdo()
    {
        return $this->innerRelation->getPdo();
    }

    /**
     * {@inheritDoc}
     */
    public function getFetcher()
    {
        return $this->innerRelation->getFetcher();
    }

    /**
     * {@inheritDoc}
     */
    public function getBuilder()
    {
        return $this->innerRelation->getBuilder();
    }

    /**
     * {@inheritDoc}
     */
    public function getJoinStrategy()
    {
        return $this->innerRelation->getJoinStrategy();
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

        $innerClass = $this->innerRelation->getFetcher()->getClass();

        if (!empty($uncachedOuterKeys)) {
            $result = $this->innerRelation->getResult($uncachedOuterKeys);
            $innerKeySelector = $this->innerRelation->resolveInnerKeySelector($innerClass);
            $freshCacheItems = [];

            foreach ($result as $element) {
                $innerKey = $innerKeySelector($element);
                $cacheKey = $this->cachePrefix . $innerKey;

                $freshCacheItems[$cacheKey] = $element;

                $cachedElements[] = $element;
            }

            $this->cache->setMultiple($freshCacheItems, $this->cacheTtl);
        }

        return new PreloadResultSet($cachedElements, $innerClass);
    }

    /**
     * {@inheritDoc}
     */
    public function resolveOuterKeySelector($outerClass)
    {
        return $this->innerRelation->resolveOuterKeySelector($outerClass);
    }

    /**
     * {@inheritDoc}
     */
    public function resolveInnerKeySelector($innerClass)
    {
        return $this->innerRelation->resolveInnerKeySelector($innerClass);
    }

    /**
     * {@inheritDoc}
     */
    public function resolveResultSelector($outerClass, $innerClass)
    {
        return $this->innerRelation->resolveResultSelector($outerClass, $innerClass);
    }

    /**
     * {@inheritDoc}
     */
    public function with(RelationInterface $relation)
    {
        return new CachedRelation(
            $this->innerRelation->with($relation),
            $this->cache,
            $this->cachePrefix,
            $this->cacheTtl
        );
    }
}
