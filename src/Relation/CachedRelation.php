<?php

namespace Emonkak\Orm\Relation;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\Relation\JoinStrategy\JoinStrategyInterface;
use Emonkak\Orm\ResultSet\FrozenResultSet;
use Emonkak\Orm\ResultSet\ResultSetInterface;
use Emonkak\Orm\SelectBuilder;
use Psr\Cache\CacheItemPoolInterface;

class CachedRelation extends Relation
{
    /**
     * @var CacheItemPoolInterface
     */
    private $cachePool;

    /**
     * @var string
     */
    private $cachePrefix;

    /**
     * @var integer
     */
    private $cacheLifetime;

    /**
     * @param string                 $relationKey
     * @param string                 $table
     * @param string                 $outerKey
     * @param string                 $innerKey
     * @param PDOInterface           $pdo
     * @param FetcherInterface       $fetcher
     * @param CacheItemPoolInterface $cachePool
     * @param string                 $cachePrefix
     * @param integer                $cacheLifetime
     * @param SelectBuilder          $builder
     * @param JoinStrategyInterface  $joinStrategy
     */
    public function __construct(
        $relationKey,
        $table,
        $outerKey,
        $innerKey,
        PDOInterface $pdo,
        FetcherInterface $fetcher,
        CacheItemPoolInterface $cachePool,
        $cachePrefix,
        $cacheLifetime,
        SelectBuilder $builder,
        JoinStrategyInterface $joinStrategy
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

        $this->cachePool = $cachePool;
        $this->cachePrefix = $cachePrefix;
        $this->cacheLifetime = $cacheLifetime;
    }

    /**
     * @return CacheItemPoolInterface
     */
    public function getCachePool()
    {
        return $this->cachePool;
    }

    /**
     * @return string
     */
    public function getCachePrefix()
    {
        return $this->cachePrefix;
    }

    /**
     * @return integer
     */
    public function getCacheLifetime()
    {
        return $this->cacheLifetime;
    }

    /**
     * {@inheritDoc}
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
            $this->cachePool,
            $this->cachePrefix,
            $this->cacheLifetime,
            $this->builder->with($relation),
            $this->joinStrategy
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

        $cacheItems = $this->cachePool->getItems($cacheKeys);
        $uncachedItems = [];
        $cachedElements = [];

        foreach ($cacheItems as $cacheItem) {
            if ($cacheItem->isHit()) {
                $cachedElements[] = $cacheItem->get();
            } else {
                $key = substr($cacheItem->getKey(), $prefixLength);
                $uncachedItems[$key] = $cacheItem;
            }
        }

        $innerClass = $this->fetcher->getClass();

        if (!empty($uncachedItems)) {
            $result = parent::getResult(array_keys($uncachedItems));
            $innerKeySelector = $this->resolveInnerKeySelector($innerClass);

            foreach ($result as $element) {
                $key = $innerKeySelector($element);

                if (isset($uncachedItems[$key])) {
                    $cacheItem = $uncachedItems[$key]->set($element);
                    if ($this->cacheLifetime !== null) {
                        $cacheItem->expiresAfter($this->cacheLifetime);
                    }
                    $this->cachePool->saveDeferred($cacheItem);
                }

                $cachedElements[] = $element;
            }

            $this->cachePool->commit();
        }

        return new FrozenResultSet($cachedElements, $innerClass);
    }
}
