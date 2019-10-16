<?php

namespace Emonkak\Orm\Relation;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\Relation\JoinStrategy\GroupJoin;
use Emonkak\Orm\Relation\JoinStrategy\LazyGroupJoin;
use Emonkak\Orm\Relation\JoinStrategy\LazyOuterJoin;
use Emonkak\Orm\Relation\JoinStrategy\OuterJoin;
use Emonkak\Orm\Relation\JoinStrategy\ThroughGroupJoin;
use Emonkak\Orm\Relation\JoinStrategy\ThroughOuterJoin;
use Emonkak\Orm\SelectBuilder;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use Psr\SimpleCache\CacheInterface;

final class Relations
{
    /**
     * @param string                      $relationKey
     * @param string                      $table
     * @param string                      $outerKey
     * @param string                      $innerKey
     * @param PDOInterface                $pdo
     * @param FetcherInterface            $fetcher
     * @param SelectBuilder               $builder
     * @param array<string,SelectBuilder> $unions
     * @return Relation
     */
    public static function oneToOne(
        $relationKey,
        $table,
        $outerKey,
        $innerKey,
        PDOInterface $pdo,
        FetcherInterface $fetcher,
        SelectBuilder $builder,
        array $unions = []
    ) {
        return new Relation(
            new OneTo(
                $relationKey,
                $table,
                $outerKey,
                $innerKey,
                $pdo,
                $fetcher,
                $builder,
                $unions
            ),
            new OuterJoin()
        );
    }

    /**
     * @param string                      $relationKey
     * @param string                      $table
     * @param string                      $outerKey
     * @param string                      $innerKey
     * @param PDOInterface                $pdo
     * @param FetcherInterface            $fetcher
     * @param SelectBuilder               $builder
     * @param array<string,SelectBuilder> $unions
     * @return Relation
     */
    public static function oneToMany(
        $relationKey,
        $table,
        $outerKey,
        $innerKey,
        PDOInterface $pdo,
        FetcherInterface $fetcher,
        SelectBuilder $builder,
        array $unions = []
    ) {
        return new Relation(
            new OneTo(
                $relationKey,
                $table,
                $outerKey,
                $innerKey,
                $pdo,
                $fetcher,
                $builder,
                $unions
            ),
            new GroupJoin()
        );
    }

    /**
     * @param string                      $relationKey
     * @param string                      $table
     * @param string                      $outerKey
     * @param string                      $innerKey
     * @param string                      $throughKey
     * @param PDOInterface                $pdo
     * @param FetcherInterface            $fetcher
     * @param SelectBuilder               $builder
     * @param array<string,SelectBuilder> $unions
     * @return Relation
     */
    public static function throughOneToOne(
        $relationKey,
        $table,
        $outerKey,
        $innerKey,
        $throughKey,
        PDOInterface $pdo,
        FetcherInterface $fetcher,
        SelectBuilder $builder,
        array $unions = []
    ) {
        return new Relation(
            new OneTo(
                $relationKey,
                $table,
                $outerKey,
                $innerKey,
                $pdo,
                $fetcher,
                $builder,
                $unions
            ),
            new ThroughOuterJoin($throughKey)
        );
    }

    /**
     * @param string                      $relationKey
     * @param string                      $table
     * @param string                      $outerKey
     * @param string                      $innerKey
     * @param string                      $throughKey
     * @param PDOInterface                $pdo
     * @param FetcherInterface            $fetcher
     * @param SelectBuilder               $builder
     * @param array<string,SelectBuilder> $unions
     * @return Relation
     */
    public static function throughOneToMany(
        $relationKey,
        $table,
        $outerKey,
        $innerKey,
        $throughKey,
        PDOInterface $pdo,
        FetcherInterface $fetcher,
        SelectBuilder $builder,
        array $unions = []
    ) {
        return new Relation(
            new OneTo(
                $relationKey,
                $table,
                $outerKey,
                $innerKey,
                $pdo,
                $fetcher,
                $builder,
                $unions
            ),
            new ThroughGroupJoin($throughKey)
        );
    }

    /**
     * @param string                        $relationKey
     * @param string                        $table
     * @param string                        $outerKey
     * @param string                        $innerKey
     * @param PDOInterface                  $pdo
     * @param FetcherInterface              $fetcher
     * @param SelectBuilder                 $builder
     * @param array<string,SelectBuilder>   $unions
     * @param LazyLoadingValueHolderFactory $proxyFactory
     * @return Relation
     */
    public static function lazyOneToOne(
        $relationKey,
        $table,
        $outerKey,
        $innerKey,
        PDOInterface $pdo,
        FetcherInterface $fetcher,
        SelectBuilder $builder,
        array $unions,
        LazyLoadingValueHolderFactory $proxyFactory
    ) {
        return new Relation(
            new OneTo(
                $relationKey,
                $table,
                $outerKey,
                $innerKey,
                $pdo,
                $fetcher,
                $builder,
                $unions
            ),
            new LazyOuterJoin($proxyFactory)
        );
    }

    /**
     * @param string                        $relationKey
     * @param string                        $table
     * @param string                        $outerKey
     * @param string                        $innerKey
     * @param PDOInterface                  $pdo
     * @param FetcherInterface              $fetcher
     * @param SelectBuilder                 $builder
     * @param array<string,SelectBuilder>   $unions
     * @param LazyLoadingValueHolderFactory $proxyFactory
     * @return Relation
     */
    public static function lazyOneToMany(
        $relationKey,
        $table,
        $outerKey,
        $innerKey,
        PDOInterface $pdo,
        FetcherInterface $fetcher,
        SelectBuilder $builder,
        array $unions,
        LazyLoadingValueHolderFactory $proxyFactory
    ) {
        return new Relation(
            new OneTo(
                $relationKey,
                $table,
                $outerKey,
                $innerKey,
                $pdo,
                $fetcher,
                $builder,
                $unions
            ),
            new LazyGroupJoin($proxyFactory)
        );
    }

    /**
     * @param string                      $relationKey
     * @param string                      $table
     * @param string                      $outerKey
     * @param string                      $innerKey
     * @param PDOInterface                $pdo
     * @param FetcherInterface            $fetcher
     * @param SelectBuilder               $builder
     * @param array<string,SelectBuilder> $unions
     * @param CacheInterface              $cache
     * @param callable                    $cacheKeySelector
     * @param ?int                        $cacheTtl
     * @return Relation
     */
    public static function cachedOneToOne(
        $relationKey,
        $table,
        $outerKey,
        $innerKey,
        PDOInterface $pdo,
        FetcherInterface $fetcher,
        SelectBuilder $builder,
        array $unions,
        CacheInterface $cache,
        callable $cacheKeySelector,
        $cacheTtl = null
    ) {
        return new Relation(
            new Cached(
                new OneTo(
                    $relationKey,
                    $table,
                    $outerKey,
                    $innerKey,
                    $pdo,
                    $fetcher,
                    $builder,
                    $unions
                ),
                $cache,
                $cacheKeySelector,
                $cacheTtl
            ),
            new OuterJoin()
        );
    }

    /**
     * @param string  $relationKey
     * @param string  $outerKey
     * @param string  $innerKey
     * @param string  $innerClass
     * @param mixed[] $innerElements
     * @return Relation
     */
    public static function preloadedOneToOne(
        $relationKey,
        $outerKey,
        $innerKey,
        $innerClass,
        array $innerElements
    ) {
        return new Relation(
            new Preloaded(
                $relationKey,
                $outerKey,
                $innerKey,
                $innerClass,
                $innerElements
            ),
            new OuterJoin()
        );
    }

    /**
     * @param string  $relationKey
     * @param string  $outerKey
     * @param string  $innerKey
     * @param string  $innerClass
     * @param mixed[] $innerElements
     * @return Relation
     */
    public static function preloadedOneToMany(
        $relationKey,
        $outerKey,
        $innerKey,
        $innerClass,
        array $innerElements
    ) {
        return new Relation(
            new Preloaded(
                $relationKey,
                $outerKey,
                $innerKey,
                $innerClass,
                $innerElements
            ),
            new GroupJoin()
        );
    }

    /**
     * @param string                      $relationKey
     * @param string                      $oneToManyTable
     * @param string                      $oneToManyOuterKey
     * @param string                      $oneToManyInnerKey
     * @param string                      $manyToOneTable
     * @param string                      $manyToOneOuterKey
     * @param string                      $manyToOneInnerKey
     * @param PDOInterface                $pdo
     * @param FetcherInterface            $fetcher
     * @param SelectBuilder               $builder
     * @param array<string,SelectBuilder> $unions
     * @return Relation
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
        SelectBuilder $builder,
        array $unions = []
    ) {
        return new Relation(
            new ManyTo(
                $relationKey,
                $oneToManyTable,
                $oneToManyOuterKey,
                $oneToManyInnerKey,
                $manyToOneTable,
                $manyToOneOuterKey,
                $manyToOneInnerKey,
                $pdo,
                $fetcher,
                $builder,
                $unions
            ),
            new GroupJoin()
        );
    }

    /**
     * @param string                      $relationKey
     * @param string                      $oneToManyTable
     * @param string                      $oneToManyOuterKey
     * @param string                      $oneToManyInnerKey
     * @param string                      $manyToOneTable
     * @param string                      $manyToOneOuterKey
     * @param string                      $manyToOneInnerKey
     * @param PDOInterface                $pdo
     * @param FetcherInterface            $fetcher
     * @param SelectBuilder               $builder
     * @param array<string,SelectBuilder> $unions
     * @return Relation
     */
    public static function throughManyToMany(
        $relationKey,
        $oneToManyTable,
        $oneToManyOuterKey,
        $oneToManyInnerKey,
        $manyToOneTable,
        $manyToOneOuterKey,
        $manyToOneInnerKey,
        $throughKey,
        PDOInterface $pdo,
        FetcherInterface $fetcher,
        SelectBuilder $builder,
        array $unions = []
    ) {
        return new Relation(
            new ManyTo(
                $relationKey,
                $oneToManyTable,
                $oneToManyOuterKey,
                $oneToManyInnerKey,
                $manyToOneTable,
                $manyToOneOuterKey,
                $manyToOneInnerKey,
                $pdo,
                $fetcher,
                $builder,
                $unions
            ),
            new ThroughGroupJoin($throughKey)
        );
    }

    /**
     * @param string $morphKey
     * @param array  $polymorphics
     * @return PolymorphicRelation
     */
    public static function polymorphic($morphKey, array $polymorphics)
    {
        return new PolymorphicRelation($morphKey, $polymorphics);
    }

    /**
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }
}
