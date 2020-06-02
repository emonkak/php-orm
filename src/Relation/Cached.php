<?php

declare(strict_types=1);

namespace Emonkak\Orm\Relation;

use Emonkak\Orm\Relation\JoinStrategy\JoinStrategyInterface;
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
     * {@inheritdoc}
     */
    public function getResult(array $outerKeys, JoinStrategyInterface $joinStrategy): iterable
    {
        $outerKeysByCacheKey = [];
        $cacheKeySelector = $this->cacheKeySelector;

        foreach ($outerKeys as $outerKey) {
            $cacheKey = $cacheKeySelector($outerKey);
            $outerKeysByCacheKey[$cacheKey] = $outerKey;
        }

        $cacheItems = $this->cache->getMultiple(array_keys($outerKeysByCacheKey));
        $cachedElements = [];
        $uncachedOuterKeys = [];

        foreach ($cacheItems as $key => $value) {
            if ($value !== null) {
                $cachedElements[] = $value;
            } else {
                $uncachedOuterKeys[] = $outerKeysByCacheKey[$key];
            }
        }

        if (count($uncachedOuterKeys) > 0) {
            $result = $this->relationStrategy->getResult($uncachedOuterKeys, $joinStrategy);
            $innerKeySelector = $joinStrategy->getInnerKeySelector();
            $cachingItems = [];

            foreach ($result as $innerElement) {
                $innerKey = $innerKeySelector($innerElement);
                $cacheKey = $cacheKeySelector($innerKey);
                $cachingItems[$cacheKey] = $innerElement;
                $cachedElements[] = $innerElement;
            }

            $this->cache->setMultiple($cachingItems, $this->cacheTtl);
        }

        return $cachedElements;
    }
}
