<?php

namespace Emonkak\Orm\Relation;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\Relation\JoinStrategy\GroupJoinStrategy;
use Emonkak\Orm\Relation\JoinStrategy\LazyGroupJoinStrategy;
use Emonkak\Orm\Relation\JoinStrategy\LazyInnerJoinStrategy;
use Emonkak\Orm\Relation\JoinStrategy\OuterJoinStrategy;
use Emonkak\Orm\SelectQuery;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;

final class Relations
{
    /**
     * @param PDOInterface     $connection
     * @param FetcherInterface $fetcher
     * @param string           $table
     * @param string           $relationKey
     * @param string           $outerKey
     * @param string           $innerKey
     * @param SelectQuery|null $query
     */
    public static function oneToOne(
        PDOInterface $connection,
        FetcherInterface $fetcher,
        $table,
        $relationKey,
        $outerKey,
        $innerKey,
        SelectQuery $query = null
    ) {
        return new Relation(
            $connection,
            $fetcher,
            $table,
            $relationKey,
            $outerKey,
            $innerKey,
            $query ?: new SelectQuery(),
            new OuterJoinStrategy()
        );
    }

    /**
     * @param PDOInterface     $connection
     * @param FetcherInterface $fetcher
     * @param string           $table
     * @param string           $relationKey
     * @param string           $outerKey
     * @param string           $innerKey
     * @param SelectQuery|null $query
     */
    public static function oneToMany(
        PDOInterface $connection,
        FetcherInterface $fetcher,
        $table,
        $relationKey,
        $outerKey,
        $innerKey,
        SelectQuery $query = null
    ) {
        return new Relation(
            $connection,
            $fetcher,
            $table,
            $relationKey,
            $outerKey,
            $innerKey,
            $query ?: new SelectQuery(),
            new GroupJoinStrategy()
        );
    }

    /**
     * @param PDOInterface                       $connection
     * @param FetcherInterface                   $fetcher
     * @param string                             $table
     * @param string                             $relationKey
     * @param string                             $outerKey
     * @param string                             $innerKey
     * @param SelectQuery|null                   $query
     * @param LazyLoadingValueHolderFactory|null $proxyFactory
     */
    public static function lazyOneToOne(
        PDOInterface $connection,
        FetcherInterface $fetcher,
        $table,
        $relationKey,
        $outerKey,
        $innerKey,
        SelectQuery $query = null,
        LazyLoadingValueHolderFactory $proxyFactory = null
    ) {
        return new Relation(
            $connection,
            $fetcher,
            $table,
            $relationKey,
            $outerKey,
            $innerKey,
            $query ?: new SelectQuery(),
            new LazyInnerJoinStrategy($proxyFactory ?: new LazyLoadingValueHolderFactory())
        );
    }

    /**
     * @param PDOInterface                       $connection
     * @param FetcherInterface                   $fetcher
     * @param string                             $table
     * @param string                             $relationKey
     * @param string                             $outerKey
     * @param string                             $innerKey
     * @param SelectQuery|null                   $query
     * @param LazyLoadingValueHolderFactory|null $proxyFactory
     */
    public static function lazyOneToMany(
        PDOInterface $connection,
        FetcherInterface $fetcher,
        $table,
        $relationKey,
        $outerKey,
        $innerKey,
        SelectQuery $query = null,
        LazyLoadingValueHolderFactory $proxyFactory = null
    ) {
        return new Relation(
            $connection,
            $fetcher,
            $table,
            $relationKey,
            $outerKey,
            $innerKey,
            $query ?: new SelectQuery(),
            new LazyGroupJoinStrategy($proxyFactory ?: new LazyLoadingValueHolderFactory())
        );
    }

    /**
     * @param string   $relationKey
     * @param Relation $oneToMany
     * @param Relation $manyToOne
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
