<?php

declare(strict_types=1);

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
     * @param array<string,SelectBuilder> $unions
     */
    public static function oneToOne(
        string $relationKey,
        string $table,
        string $outerKey,
        string $innerKey,
        PDOInterface $pdo,
        FetcherInterface $fetcher,
        SelectBuilder $queryBuilder,
        array $unions = []
    ): Relation {
        return new Relation(
            new OneTo(
                $relationKey,
                $table,
                $outerKey,
                $innerKey,
                $pdo,
                $fetcher,
                $queryBuilder,
                $unions
            ),
            new OuterJoin()
        );
    }

    /**
     * @param array<string,SelectBuilder> $unions
     */
    public static function oneToMany(
        string $relationKey,
        string $table,
        string $outerKey,
        string $innerKey,
        PDOInterface $pdo,
        FetcherInterface $fetcher,
        SelectBuilder $queryBuilder,
        array $unions = []
    ): Relation {
        return new Relation(
            new OneTo(
                $relationKey,
                $table,
                $outerKey,
                $innerKey,
                $pdo,
                $fetcher,
                $queryBuilder,
                $unions
            ),
            new GroupJoin()
        );
    }

    /**
     * @param array<string,SelectBuilder> $unions
     */
    public static function throughOneToOne(
        $relationKey,
        $table,
        $outerKey,
        $innerKey,
        $throughKey,
        PDOInterface $pdo,
        FetcherInterface $fetcher,
        SelectBuilder $queryBuilder,
        array $unions = []
    ): Relation {
        return new Relation(
            new OneTo(
                $relationKey,
                $table,
                $outerKey,
                $innerKey,
                $pdo,
                $fetcher,
                $queryBuilder,
                $unions
            ),
            new ThroughOuterJoin($throughKey)
        );
    }

    /**
     * @param array<string,SelectBuilder> $unions
     */
    public static function throughOneToMany(
        $relationKey,
        $table,
        $outerKey,
        $innerKey,
        $throughKey,
        PDOInterface $pdo,
        FetcherInterface $fetcher,
        SelectBuilder $queryBuilder,
        array $unions = []
    ): Relation {
        return new Relation(
            new OneTo(
                $relationKey,
                $table,
                $outerKey,
                $innerKey,
                $pdo,
                $fetcher,
                $queryBuilder,
                $unions
            ),
            new ThroughGroupJoin($throughKey)
        );
    }

    /**
     * @param array<string,SelectBuilder> $unions
     */
    public static function lazyOneToOne(
        $relationKey,
        $table,
        $outerKey,
        $innerKey,
        PDOInterface $pdo,
        FetcherInterface $fetcher,
        SelectBuilder $queryBuilder,
        array $unions,
        LazyLoadingValueHolderFactory $proxyFactory
    ): Relation {
        return new Relation(
            new OneTo(
                $relationKey,
                $table,
                $outerKey,
                $innerKey,
                $pdo,
                $fetcher,
                $queryBuilder,
                $unions
            ),
            new LazyOuterJoin($proxyFactory)
        );
    }

    /**
     * @param array<string,SelectBuilder> $unions
     */
    public static function lazyOneToMany(
        $relationKey,
        $table,
        $outerKey,
        $innerKey,
        PDOInterface $pdo,
        FetcherInterface $fetcher,
        SelectBuilder $queryBuilder,
        array $unions,
        LazyLoadingValueHolderFactory $proxyFactory
    ): Relation {
        return new Relation(
            new OneTo(
                $relationKey,
                $table,
                $outerKey,
                $innerKey,
                $pdo,
                $fetcher,
                $queryBuilder,
                $unions
            ),
            new LazyGroupJoin($proxyFactory)
        );
    }

    /**
     * @param array<string,SelectBuilder> $unions
     */
    public static function cachedOneToOne(
        $relationKey,
        $table,
        $outerKey,
        $innerKey,
        PDOInterface $pdo,
        FetcherInterface $fetcher,
        SelectBuilder $queryBuilder,
        array $unions,
        CacheInterface $cache,
        callable $cacheKeySelector,
        $cacheTtl = null
    ): Relation {
        return new Relation(
            new Cached(
                new OneTo(
                    $relationKey,
                    $table,
                    $outerKey,
                    $innerKey,
                    $pdo,
                    $fetcher,
                    $queryBuilder,
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
     * @param mixed[] $innerElements
     */
    public static function preloadedOneToOne(
        $relationKey,
        $outerKey,
        $innerKey,
        $innerClass,
        array $innerElements
    ): Relation {
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
     * @param mixed[] $innerElements
     */
    public static function preloadedOneToMany(
        $relationKey,
        $outerKey,
        $innerKey,
        $innerClass,
        array $innerElements
    ): Relation {
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
     * @param SelectBuilder               $queryBuilder
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
        SelectBuilder $queryBuilder,
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
                $queryBuilder,
                $unions
            ),
            new GroupJoin()
        );
    }

    /**
     * @param array<string,SelectBuilder> $unions
     */
    public static function throughManyToMany(
        string $relationKey,
        string $oneToManyTable,
        string $oneToManyOuterKey,
        string $oneToManyInnerKey,
        string $manyToOneTable,
        string $manyToOneOuterKey,
        string $manyToOneInnerKey,
        string $throughKey,
        PDOInterface $pdo,
        FetcherInterface $fetcher,
        SelectBuilder $queryBuilder,
        array $unions = []
    ): Relation {
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
                $queryBuilder,
                $unions
            ),
            new ThroughGroupJoin($throughKey)
        );
    }

    /**
     * @param array<string,RelationInterface> $polymorphics
     * @return PolymorphicRelation
     */
    public static function polymorphic(string $morphKey, array $polymorphics)
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
