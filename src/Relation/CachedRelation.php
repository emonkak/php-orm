<?php

namespace Emonkak\Orm\Relation;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\Relation\JoinStrategy\JoinStrategyInterface;
use Emonkak\Orm\ResultSet\ResultSetInterface;
use Emonkak\Orm\SelectQuery;
use Psr\Cache\CacheItemPoolInterface;

class CachedRelation extends AbstractRelation
{
    /**
     * @var CacheItemPoolInterface
     */
    private $cachePool;

    /**
     * @var integer|null
     */
    private $lifetime;

    /**
     * @param string                 $table
     * @param string                 $relationKey
     * @param string                 $outerKey
     * @param string                 $innerKey
     * @param PDOInterface           $connection
     * @param FetcherInterface       $fetcher
     * @param CacheItemPoolInterface $cachePool
     * @param integer|null           $lifetime
     * @param SelectQuery            $query
     * @param JoinStrategyInterface  $joinStrategy
     */
    public function __construct(
        $table,
        $relationKey,
        $outerKey,
        $innerKey,
        PDOInterface $connection,
        FetcherInterface $fetcher,
        CacheItemPoolInterface $cachePool,
        $lifetime,
        SelectQuery $query,
        JoinStrategyInterface $joinStrategy
    ) {
        parent::__construct(
            $table,
            $relationKey,
            $outerKey,
            $innerKey,
            $connection,
            $fetcher,
            $query,
            $joinStrategy
        );

        $this->cachePool = $cachePool;
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
            $this->connection,
            $this->fetcher,
            $this->cachePool,
            $this->lifetime,
            $this->query->with($relation),
            $this->joinStrategy
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function getResult($outerKeys)
    {
        $prefix = $this->getCachePrefix();
        $prefixLength = strlen($prefix);
        $cacheKeys = [];

        foreach ($outerKeys as $outerKey) {
            $cacheKeys[] = $prefix . $outerKey;
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
            $result = $this->query
                ->from(sprintf('`%s`', $this->table))
                ->where(sprintf('`%s`.`%s`', $this->table, $this->innerKey), 'IN', array_keys($uncachedItems))
                ->getResult($this->connection, $this->fetcher);

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

    /**
     * @return string
     */
    protected function getCachePrefix()
    {
        return $this->table . '.' . $this->innerKey . '.';
    }
}
