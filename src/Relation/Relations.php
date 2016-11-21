<?php

namespace Emonkak\Orm\Relation;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\Relation\JoinStrategy\GroupJoin;
use Emonkak\Orm\Relation\JoinStrategy\LazyGroupJoin;
use Emonkak\Orm\Relation\JoinStrategy\LazyOuterJoin;
use Emonkak\Orm\Relation\JoinStrategy\OuterJoin;
use Emonkak\Orm\Relation\JoinStrategy\ThroughGroupJoin;
use Emonkak\Orm\SelectBuilder;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use Psr\Cache\CacheItemPoolInterface;

final class Relations
{
    /**
     * @param string             $relationKey
     * @param string             $table
     * @param string             $outerKey
     * @param string             $innerKey
     * @param PDOInterface       $pdo
     * @param FetcherInterface   $fetcher
     * @param SelectBuilder|null $builder
     * @return Relation
     */
    public static function oneToOne(
        $relationKey,
        $table,
        $outerKey,
        $innerKey,
        PDOInterface $pdo,
        FetcherInterface $fetcher,
        SelectBuilder $builder = null
    ) {
        return new Relation(
            $relationKey,
            $table,
            $outerKey,
            $innerKey,
            $pdo,
            $fetcher,
            $builder ?: new SelectBuilder(),
            new OuterJoin()
        );
    }

    /**
     * @param string             $relationKey
     * @param string             $table
     * @param string             $outerKey
     * @param string             $innerKey
     * @param PDOInterface       $pdo
     * @param FetcherInterface   $fetcher
     * @param SelectBuilder|null $builder
     * @return Relation
     */
    public static function oneToMany(
        $relationKey,
        $table,
        $outerKey,
        $innerKey,
        PDOInterface $pdo,
        FetcherInterface $fetcher,
        SelectBuilder $builder = null
    ) {
        return new Relation(
            $relationKey,
            $table,
            $outerKey,
            $innerKey,
            $pdo,
            $fetcher,
            $builder ?: new SelectBuilder(),
            new GroupJoin()
        );
    }

    /**
     * @param string             $relationKey
     * @param string             $throughKey
     * @param string             $table
     * @param string             $outerKey
     * @param string             $innerKey
     * @param PDOInterface       $pdo
     * @param FetcherInterface   $fetcher
     * @param SelectBuilder|null $builder
     * @return Relation
     */
    public static function throughOneToMany(
        $relationKey,
        $throughKey,
        $table,
        $outerKey,
        $innerKey,
        PDOInterface $pdo,
        FetcherInterface $fetcher,
        SelectBuilder $builder = null
    ) {
        return new Relation(
            $relationKey,
            $table,
            $outerKey,
            $innerKey,
            $pdo,
            $fetcher,
            $builder ?: new SelectBuilder(),
            new ThroughGroupJoin($throughKey)
        );
    }

    /**
     * @param string                             $relationKey
     * @param string                             $table
     * @param string                             $outerKey
     * @param string                             $innerKey
     * @param PDOInterface                       $pdo
     * @param FetcherInterface                   $fetcher
     * @param LazyLoadingValueHolderFactory|null $proxyFactory
     * @param SelectBuilder|null                 $builder
     * @return Relation
     */
    public static function lazyOneToOne(
        $relationKey,
        $table,
        $outerKey,
        $innerKey,
        PDOInterface $pdo,
        FetcherInterface $fetcher,
        LazyLoadingValueHolderFactory $proxyFactory = null,
        SelectBuilder $builder = null
    ) {
        return new Relation(
            $relationKey,
            $table,
            $outerKey,
            $innerKey,
            $pdo,
            $fetcher,
            $builder ?: new SelectBuilder(),
            new LazyOuterJoin($proxyFactory ?: new LazyLoadingValueHolderFactory())
        );
    }

    /**
     * @param string                             $relationKey
     * @param string                             $table
     * @param string                             $outerKey
     * @param string                             $innerKey
     * @param PDOInterface                       $pdo
     * @param FetcherInterface                   $fetcher
     * @param LazyLoadingValueHolderFactory|null $proxyFactory
     * @param SelectBuilder|null                 $builder
     * @return Relation
     */
    public static function lazyOneToMany(
        $relationKey,
        $table,
        $outerKey,
        $innerKey,
        PDOInterface $pdo,
        FetcherInterface $fetcher,
        LazyLoadingValueHolderFactory $proxyFactory = null,
        SelectBuilder $builder = null
    ) {
        return new Relation(
            $relationKey,
            $table,
            $outerKey,
            $innerKey,
            $pdo,
            $fetcher,
            $builder ?: new SelectBuilder(),
            new LazyGroupJoin($proxyFactory ?: new LazyLoadingValueHolderFactory())
        );
    }

    /**
     * @param string                 $relationKey
     * @param string                 $table
     * @param string                 $outerKey
     * @param string                 $innerKey
     * @param PDOInterface           $pdo
     * @param FetcherInterface       $fetcher
     * @param CacheItemPoolInterface $cachePool
     * @param string                 $cachePrefix
     * @param integer|null           $cacheLifetime
     * @param SelectBuilder|null     $builder
     * @return CachedRelation
     */
    public static function cachedOneToOne(
        $relationKey,
        $table,
        $outerKey,
        $innerKey,
        PDOInterface $pdo,
        FetcherInterface $fetcher,
        CacheItemPoolInterface $cachePool,
        $cachePrefix,
        $cacheLifetime = null,
        SelectBuilder $builder = null
    ) {
        return new CachedRelation(
            $relationKey,
            $table,
            $outerKey,
            $innerKey,
            $pdo,
            $fetcher,
            $cachePool,
            $cachePrefix,
            $cacheLifetime,
            $builder ?: new SelectBuilder(),
            new OuterJoin()
        );
    }

    /**
     * @param string                 $relationKey
     * @param string                 $table
     * @param string                 $outerKey
     * @param string                 $innerKey
     * @param PDOInterface           $pdo
     * @param FetcherInterface       $fetcher
     * @param CacheItemPoolInterface $cachePool
     * @param string                 $cachePrefix
     * @param integer|null           $cacheLifetime
     * @param SelectBuilder|null     $builder
     * @return CachedRelation
     */
    public static function cachedOneToMany(
        $relationKey,
        $table,
        $outerKey,
        $innerKey,
        PDOInterface $pdo,
        FetcherInterface $fetcher,
        CacheItemPoolInterface $cachePool,
        $cachePrefix,
        $cacheLifetime = null,
        SelectBuilder $builder = null
    ) {
        return new CachedRelation(
            $relationKey,
            $table,
            $outerKey,
            $innerKey,
            $pdo,
            $fetcher,
            $cachePool,
            $cachePrefix,
            $cacheLifetime,
            $builder ?: new SelectBuilder(),
            new GroupJoin()
        );
    }

    /**
     * @param string                $relationKey
     * @param string                $oneToManyTable
     * @param string                $oneToManyOuterKey
     * @param string                $oneToManyInnerKey
     * @param string                $manyToOneTable
     * @param string                $manyToOneOuterKey
     * @param string                $manyToOneInnerKey
     * @param SelectBuilder         $builder
     * @param PDOInterface          $pdo
     * @param FetcherInterface      $fetcher
     * @param SelectBuilder|null    $builder
     * @return ManyToMany
     */
    public static function manyToMany(
        $relationKey,
        $oneToManyTable,
        $oneToManyOuterKey,
        $oneToManyInnerKey,
        $manyToOneTable,
        $manyToOneOuterKey,
        $manyToOneInnerKey,
        PDOInterface $pdo,
        FetcherInterface $fetcher,
        SelectBuilder $builder = null
    ) {
        return new ManyToMany(
            $relationKey,
            $oneToManyTable,
            $oneToManyOuterKey,
            $oneToManyInnerKey,
            $manyToOneTable,
            $manyToOneOuterKey,
            $manyToOneInnerKey,
            $pdo,
            $fetcher,
            $builder ?: new SelectBuilder(),
            new GroupJoin()
        );
    }

    /**
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }
}
