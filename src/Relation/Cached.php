<?php

declare(strict_types=1);

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
     * @param callable(mixed):mixed $cacheKeySelector
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

    public function getInnerRelationStrategy(): RelationStrategyInterface
    {
        return $this->relationStrategy;
    }

    public function getCache(): CacheInterface
    {
        return $this->cache;
    }

    /**
     * @return callable(mixed):mixed
     */
    public function getCacheKeySelector(): callable
    {
        return $this->cacheKeySelector;
    }

    public function getCacheTtl(): ?int
    {
        return $this->cacheTtl;
    }

    public function getResult(array $outerKeys): ResultSetInterface
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

    public function getOuterKeySelector(?string $outerClass): callable
    {
        return $this->relationStrategy->getOuterKeySelector($outerClass);
    }

    public function getInnerKeySelector(?string $innerClass): callable
    {
        return $this->relationStrategy->getInnerKeySelector($innerClass);
    }

    public function getResultSelector(?string $outerClass, ?string $innerClass): callable
    {
        return $this->relationStrategy->getResultSelector($outerClass, $innerClass);
    }
}
