<?php

namespace Emonkak\Orm\Relation;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\Relation\JoinStrategy\GroupJoin;
use Emonkak\Orm\Relation\JoinStrategy\LazyGroupJoin;
use Emonkak\Orm\Relation\JoinStrategy\LazyInnerJoin;
use Emonkak\Orm\Relation\JoinStrategy\OuterJoin;
use Emonkak\Orm\SelectQuery;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use Psr\Cache\CacheItemPoolInterface;

final class Relations
{
    /**
     * @param string           $table
     * @param string           $relationKey
     * @param string           $outerKey
     * @param string           $innerKey
     * @param PDOInterface     $connection
     * @param FetcherInterface $fetcher
     * @param SelectQuery|null $query
     * @return Relation
     */
    public static function oneToOne(
        $table,
        $relationKey,
        $outerKey,
        $innerKey,
        PDOInterface $connection,
        FetcherInterface $fetcher,
        SelectQuery $query = null
    ) {
        return new Relation(
            $table,
            $relationKey,
            $outerKey,
            $innerKey,
            $connection,
            $fetcher,
            $query ?: new SelectQuery(),
            new OuterJoin()
        );
    }

    /**
     * @param string           $table
     * @param string           $relationKey
     * @param string           $outerKey
     * @param string           $innerKey
     * @param PDOInterface     $connection
     * @param FetcherInterface $fetcher
     * @param SelectQuery|null $query
     * @return Relation
     */
    public static function oneToMany(
        $table,
        $relationKey,
        $outerKey,
        $innerKey,
        PDOInterface $connection,
        FetcherInterface $fetcher,
        SelectQuery $query = null
    ) {
        return new Relation(
            $table,
            $relationKey,
            $outerKey,
            $innerKey,
            $connection,
            $fetcher,
            $query ?: new SelectQuery(),
            new GroupJoin()
        );
    }

    /**
     * @param string                             $table
     * @param string                             $relationKey
     * @param string                             $outerKey
     * @param string                             $innerKey
     * @param PDOInterface                       $connection
     * @param FetcherInterface                   $fetcher
     * @param LazyLoadingValueHolderFactory|null $proxyFactory
     * @param SelectQuery|null                   $query
     * @return Relation
     */
    public static function lazyOneToOne(
        $table,
        $relationKey,
        $outerKey,
        $innerKey,
        PDOInterface $connection,
        FetcherInterface $fetcher,
        LazyLoadingValueHolderFactory $proxyFactory = null,
        SelectQuery $query = null
    ) {
        return new Relation(
            $table,
            $relationKey,
            $outerKey,
            $innerKey,
            $connection,
            $fetcher,
            $query ?: new SelectQuery(),
            new LazyInnerJoin($proxyFactory ?: new LazyLoadingValueHolderFactory())
        );
    }

    /**
     * @param string                             $table
     * @param string                             $relationKey
     * @param string                             $outerKey
     * @param string                             $innerKey
     * @param PDOInterface                       $connection
     * @param FetcherInterface                   $fetcher
     * @param LazyLoadingValueHolderFactory|null $proxyFactory
     * @param SelectQuery|null                   $query
     * @return Relation
     */
    public static function lazyOneToMany(
        $table,
        $relationKey,
        $outerKey,
        $innerKey,
        PDOInterface $connection,
        FetcherInterface $fetcher,
        LazyLoadingValueHolderFactory $proxyFactory = null,
        SelectQuery $query = null
    ) {
        return new Relation(
            $table,
            $relationKey,
            $outerKey,
            $innerKey,
            $connection,
            $fetcher,
            $query ?: new SelectQuery(),
            new LazyGroupJoin($proxyFactory ?: new LazyLoadingValueHolderFactory())
        );
    }

    /**
     * @param string                 $table
     * @param string                 $relationKey
     * @param string                 $outerKey
     * @param string                 $innerKey
     * @param PDOInterface           $connection
     * @param FetcherInterface       $fetcher
     * @param CacheItemPoolInterface $cachePool
     * @param integer|null           $lifetime
     * @param SelectQuery|null       $query
     * @return CachedRelation
     */
    public static function cachedOneToOne(
        $table,
        $relationKey,
        $outerKey,
        $innerKey,
        PDOInterface $connection,
        FetcherInterface $fetcher,
        CacheItemPoolInterface $cachePool,
        $lifetime = null,
        SelectQuery $query = null
    ) {
        return new CachedRelation(
            $table,
            $relationKey,
            $outerKey,
            $innerKey,
            $connection,
            $fetcher,
            $cachePool,
            $lifetime,
            $query ?: new SelectQuery(),
            new OuterJoin()
        );
    }

    /**
     * @param string                 $table
     * @param string                 $relationKey
     * @param string                 $outerKey
     * @param string                 $innerKey
     * @param PDOInterface           $connection
     * @param FetcherInterface       $fetcher
     * @param CacheItemPoolInterface $cachePool
     * @param integer|null           $lifetime
     * @param SelectQuery|null       $query
     * @return CachedRelation
     */
    public static function cachedOneToMany(
        $table,
        $relationKey,
        $outerKey,
        $innerKey,
        PDOInterface $connection,
        FetcherInterface $fetcher,
        CacheItemPoolInterface $cachePool,
        $lifetime = null,
        SelectQuery $query = null
    ) {
        return new CachedRelation(
            $table,
            $relationKey,
            $outerKey,
            $innerKey,
            $connection,
            $fetcher,
            $cachePool,
            $lifetime,
            $query ?: new SelectQuery(),
            new GroupJoin()
        );
    }

    /**
     * @param string   $relationKey
     * @param Relation $oneToMany
     * @param Relation $manyToOne
     * @return ManyToMany
     */
    public static function manyToMany($relationKey, Relation $oneToMany, Relation $manyToOne)
    {
        return new ManyToMany($relationKey, $oneToMany, $manyToOne);
    }

    /**
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }
}
