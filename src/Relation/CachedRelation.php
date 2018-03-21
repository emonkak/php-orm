<?php

namespace Emonkak\Orm\Relation;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\Relation\JoinStrategy\JoinStrategyInterface;
use Emonkak\Orm\ResultSet\PreloadResultSet;
use Emonkak\Orm\ResultSet\ResultSetInterface;
use Emonkak\Orm\SelectBuilder;
use Psr\SimpleCache\CacheInterface;

class CachedRelation extends Relation
{
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
     * @param string                     $relationKey
     * @param string                     $table
     * @param string                     $outerKey
     * @param string                     $innerKey
     * @param PDOInterface               $pdo
     * @param FetcherInterface           $fetcher
     * @param SelectBuilder              $builder
     * @param JoinStrategyInterface      $joinStrategy
     * @param CacheInterface             $cache
     * @param string                     $cachePrefix
     * @param integer|\DateInterval|null $cacheTtl
     */
    public function __construct(
        $relationKey,
        $table,
        $outerKey,
        $innerKey,
        PDOInterface $pdo,
        FetcherInterface $fetcher,
        SelectBuilder $builder,
        JoinStrategyInterface $joinStrategy,
        CacheInterface $cache,
        $cachePrefix,
        $cacheTtl
    ) {
        parent::__construct(
            $relationKey,
            $table,
            $outerKey,
            $innerKey,
            $pdo,
            $fetcher,
            $builder,
            $joinStrategy
        );

        $this->cache = $cache;
        $this->cachePrefix = $cachePrefix;
        $this->cacheTtl = $cacheTtl;
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
     * Adds the relation to this relation.
     *
     * @param RelationInterface $relation
     * @return CachedRelation
     */
    public function with(RelationInterface $relation)
    {
        return new CachedRelation(
            $this->relationKey,
            $this->table,
            $this->outerKey,
            $this->innerKey,
            $this->pdo,
            $this->fetcher,
            $this->builder->with($relation),
            $this->joinStrategy,
            $this->cache,
            $this->cachePrefix,
            $this->cacheTtl
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function getResult($outerKeys)
    {
        $prefixLength = strlen($this->cachePrefix);
        $cacheKeys = [];

        foreach ($outerKeys as $outerKey) {
            $cacheKeys[] = $this->cachePrefix . $outerKey;
        }

        $cacheItems = $this->cache->getMultiple($cacheKeys);
        $cachedElements = [];
        $uncachedKeys = [];

        foreach ($cacheItems as $key => $value) {
            if ($value !== null) {
                $cachedElements[] = $value;
            } else {
                $uncachedKeys[] = substr($key, $prefixLength);
            }
        }

        $innerClass = $this->fetcher->getClass();

        if (!empty($uncachedKeys)) {
            $result = parent::getResult($uncachedKeys);
            $innerKeySelector = $this->resolveInnerKeySelector($innerClass);
            $freshCacheItems = [];

            foreach ($result as $element) {
                $cacheKey = $this->cachePrefix . $innerKeySelector($element);

                $freshCacheItems[$cacheKey] = $element;

                $cachedElements[] = $element;
            }

            $this->cache->setMultiple($freshCacheItems, $this->cacheTtl);
        }

        return new PreloadResultSet($cachedElements, $innerClass);
    }
}
