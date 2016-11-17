<?php

namespace Emonkak\Orm\Relation;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\Relation\JoinStrategy\JoinStrategyInterface;
use Emonkak\Orm\ResultSet\ResultSetInterface;
use Emonkak\Orm\SelectBuilder;
use Psr\Cache\CacheItemPoolInterface;

class CachedRelation extends AbstractRelation
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
     * @var integer|null
     */
    private $lifetime;

    /**
     * @param string                 $table
     * @param string                 $relationKey
     * @param string                 $outerKey
     * @param string                 $innerKey
     * @param PDOInterface           $pdo
     * @param FetcherInterface       $fetcher
     * @param CacheItemPoolInterface $cachePool
     * @param string                 $cachePrefix
     * @param integer|null           $lifetime
     * @param SelectBuilder          $builder
     * @param JoinStrategyInterface  $joinStrategy
     */
    public function __construct(
        $table,
        $relationKey,
        $outerKey,
        $innerKey,
        PDOInterface $pdo,
        FetcherInterface $fetcher,
        CacheItemPoolInterface $cachePool,
        $cachePrefix,
        $lifetime,
        SelectBuilder $builder,
        JoinStrategyInterface $joinStrategy
    ) {
        parent::__construct(
            $table,
            $relationKey,
            $outerKey,
            $innerKey,
            $pdo,
            $fetcher,
            $builder,
            $joinStrategy
        );

        $this->cachePool = $cachePool;
        $this->cachePrefix = $cachePrefix;
        $this->lifetime = $lifetime;
    }

    /**
     * {@inheritDoc}
     */
    public function with(RelationInterface $relation)
    {
        return new CachedRelation(
            $this->table,
            $this->relationKey,
            $this->outerKey,
            $this->innerKey,
            $this->pdo,
            $this->fetcher,
            $this->cachePool,
            $this->cachePrefix,
            $this->lifetime,
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

        $items = $this->cachePool->getItems($cacheKeys);
        $cachedElements = [];
        $uncachedItems = [];

        foreach ($items as $item) {
            if ($item->isHit()) {
                $cachedElements[] = $item->get();
            } else {
                $key = substr($item->getKey(), $prefixLength);
                $uncachedItems[$key] = $item;
            }
        }

        if (!empty($uncachedItems)) {
            $grammar = $this->builder->getGrammar();

            $result = $this->builder
                ->from($grammar->identifier($this->table))
                ->where($grammar->identifier($this->table) . '.' . $grammar->identifier($this->innerKey), 'IN', array_keys($uncachedItems))
                ->getResult($this->pdo, $this->fetcher);

            $innerKeySelector = AccessorCreators::toKeySelector($this->innerKey, $this->fetcher->getClass());

            foreach ($result as $element) {
                $key = $innerKeySelector($element);

                if (isset($uncachedItems[$key])) {
                    $item = $uncachedItems[$key]->set($element)->expiresAfter($this->lifetime);
                    $this->cachePool->saveDeferred($item);
                }

                $cachedElements[] = $element;
            }

            $this->cachePool->commit();
        }

        return $cachedElements;
    }
}
