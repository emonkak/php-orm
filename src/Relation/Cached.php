<?php

declare(strict_types=1);

namespace Emonkak\Orm\Relation;

use Emonkak\Orm\Relation\JoinStrategy\JoinStrategyInterface;
use Emonkak\Orm\ResultSet\PreloadedResultSet;
use Emonkak\Orm\ResultSet\ResultSetInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * @template TInner
 * @template TKey
 * @implements RelationStrategyInterface<TInner,TKey>
 */
class Cached implements RelationStrategyInterface
{
    /**
     * @psalm-var RelationStrategyInterface<TInner,TKey>
     * @var RelationStrategyInterface
     */
    private $relationStrategy;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @psalm-var callable(TKey):string
     */
    private $cacheKeySelector;

    /**
     * @var ?int
     */
    private $cacheTtl;

    /**
     * @psalm-param RelationStrategyInterface<TInner,TKey> $relationStrategy
     * @psalm-param callable(TKey):string $cacheKeySelector
     */
    public function __construct(
        RelationStrategyInterface $relationStrategy,
        CacheInterface $cache,
        callable $cacheKeySelector,
        ?int $cacheTtl
    ) {
        $this->relationStrategy = $relationStrategy;
        $this->cache = $cache;
        $this->cacheKeySelector = $cacheKeySelector;
        $this->cacheTtl = $cacheTtl;
    }

    /**
     * @psalm-return RelationStrategyInterface<TInner,TKey>
     */
    public function getRelationStrategy(): RelationStrategyInterface
    {
        return $this->relationStrategy;
    }

    public function getCache(): CacheInterface
    {
        return $this->cache;
    }

    /**
     * @psalm-return callable(TKey):string
     */
    public function getCacheKeySelector(): callable
    {
        return $this->cacheKeySelector;
    }

    public function getCacheTtl(): ?int
    {
        return $this->cacheTtl;
    }

    /**
     * {@inheritDoc}
     */
    public function getResult(array $outerKeys, JoinStrategyInterface $joinStrategy): ResultSetInterface
    {
        $cacheKeyIndexes = [];
        $cacheKeySelector = $this->cacheKeySelector;

        foreach ($outerKeys as $outerKey) {
            $cacheKey = $cacheKeySelector($outerKey);
            $cacheKeyIndexes[$cacheKey] = $outerKey;
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

        if (count($uncachedOuterKeys) > 0) {
            $result = $this->relationStrategy->getResult($uncachedOuterKeys, $joinStrategy);
            $innerKeySelector = $joinStrategy->getInnerKeySelector();
            $freshCacheItems = [];

            foreach ($result as $innerElement) {
                $innerKey = $innerKeySelector($innerElement);
                $cacheKey = $cacheKeySelector($innerKey);
                $freshCacheItems[$cacheKey] = $innerElement;
                $cachedElements[] = $innerElement;
            }

            $this->cache->setMultiple($freshCacheItems, $this->cacheTtl);
        }

        return new PreloadedResultSet($cachedElements);
    }
}
