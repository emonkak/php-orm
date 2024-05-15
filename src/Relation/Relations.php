<?php

declare(strict_types=1);

namespace Emonkak\Orm\Relation;

use Emonkak\Enumerable\LooseEqualityComparer;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\Relation\JoinStrategy\GroupJoin;
use Emonkak\Orm\Relation\JoinStrategy\LazyCollection;
use Emonkak\Orm\Relation\JoinStrategy\LazyGroupJoin;
use Emonkak\Orm\Relation\JoinStrategy\LazyOuterJoin;
use Emonkak\Orm\Relation\JoinStrategy\LazyValue;
use Emonkak\Orm\Relation\JoinStrategy\OuterJoin;
use Emonkak\Orm\SelectBuilder;
use Psr\SimpleCache\CacheInterface;

final class Relations
{
    /**
     * @template TOuter
     * @template TInner
     * @template TKey
     * @param FetcherInterface<TInner> $fetcher
     * @return callable(?class-string):Relation<TOuter,TInner,TKey,TOuter>
     */
    public static function oneToOne(
        string $relationKeyName,
        string $tableName,
        string $outerKeyName,
        string $innerKeyName,
        SelectBuilder $queryBuilder,
        FetcherInterface $fetcher
    ): callable {
        return
            /**
             * @param ?class-string $outerClass
             * @return Relation<TOuter,TInner,TKey,TOuter>
             */
            function(?string $outerClass) use (
                $relationKeyName,
                $tableName,
                $outerKeyName,
                $innerKeyName,
                $queryBuilder,
                $fetcher
            ): Relation {
                $innerClass = $fetcher->getClass();
                /** @var callable(TOuter):TKey */
                $outerKeySelector = AccessorCreators::createKeySelector($outerClass, $outerKeyName);
                /** @var callable(TInner):TKey */
                $innerKeySelector = AccessorCreators::createKeySelector($innerClass, $innerKeyName);
                /** @var callable(TOuter,?TInner):TOuter */
                $resultSelector = AccessorCreators::createKeyAssignor($outerClass, $relationKeyName);
                /** @var LooseEqualityComparer<TKey> */
                $comparer = LooseEqualityComparer::getInstance();
                /** @var OneTo<TInner,TKey> */
                $relationStrategy = new OneTo(
                    $relationKeyName,
                    $tableName,
                    $outerKeyName,
                    $innerKeyName,
                    $queryBuilder,
                    $fetcher
                );
                /** @var OuterJoin<TOuter,TInner,TKey,TOuter> */
                $joinStrategy = new OuterJoin(
                    $outerKeySelector,
                    $innerKeySelector,
                    $resultSelector,
                    $comparer
                );
                return new Relation($outerClass, $relationStrategy, $joinStrategy);
            };
    }

    /**
     * @template TOuter
     * @template TInner
     * @template TKey
     * @param FetcherInterface<TInner> $fetcher
     * @param ?class-string $collationClass
     * @return callable(?class-string):Relation<TOuter,TInner,TKey,TOuter>
     */
    public static function oneToMany(
        string $relationKeyName,
        string $tableName,
        string $outerKeyName,
        string $innerKeyName,
        SelectBuilder $queryBuilder,
        FetcherInterface $fetcher,
        ?string $collationClass = null
    ): callable {
        return
            /**
             * @param ?class-string $outerClass
             * @return Relation<TOuter,TInner,TKey,TOuter>
             */
            function(?string $outerClass) use (
                $relationKeyName,
                $tableName,
                $outerKeyName,
                $innerKeyName,
                $queryBuilder,
                $fetcher,
                $collationClass
            ): Relation {
                $innerClass = $fetcher->getClass();
                /** @var callable(TOuter):TKey */
                $outerKeySelector = AccessorCreators::createKeySelector($outerClass, $outerKeyName);
                /** @var callable(TInner):TKey */
                $innerKeySelector = AccessorCreators::createKeySelector($innerClass, $innerKeyName);
                /** @var callable(TOuter,iterable<TInner>):TOuter */
                $resultSelector = AccessorCreators::createKeyAssignor($outerClass, $relationKeyName);
                if ($collationClass !== null) {
                    $resultSelector =
                        /**
                         * @param TOuter $lhs
                         * @param TInner[] $rhs
                         * @return TOuter
                         */
                        function(mixed $lhs, mixed $rhs) use ($collationClass, $resultSelector): mixed {
                            /** @var iterable<TInner> */
                            $rhs = new $collationClass($rhs);
                            return $resultSelector($lhs, $rhs);
                        };
                }
                /** @var LooseEqualityComparer<TKey> */
                $comparer = LooseEqualityComparer::getInstance();
                /** @var OneTo<TInner,TKey> */
                $relationStrategy = new OneTo(
                    $relationKeyName,
                    $tableName,
                    $outerKeyName,
                    $innerKeyName,
                    $queryBuilder,
                    $fetcher
                );
                /** @var GroupJoin<TOuter,TInner,TKey,TOuter> */
                $joinStrategy = new GroupJoin(
                    $outerKeySelector,
                    $innerKeySelector,
                    $resultSelector,
                    $comparer
                );
                return new Relation($outerClass, $relationStrategy, $joinStrategy);
            };
    }

    /**
     * @template TOuter
     * @template TInner
     * @template TKey
     * @param FetcherInterface<TInner> $fetcher
     * @return callable(?class-string):Relation<TOuter,TInner,TKey,TOuter>
     */
    public static function throughOneToOne(
        string $relationKeyName,
        string $tableName,
        string $outerKeyName,
        string $innerKeyName,
        string $throughKeyName,
        SelectBuilder $queryBuilder,
        FetcherInterface $fetcher
    ): callable {
        return
            /**
             * @param ?class-string $outerClass
             * @return Relation<TOuter,TInner,TKey,TOuter>
             */
            function(?string $outerClass) use (
                $relationKeyName,
                $tableName,
                $outerKeyName,
                $innerKeyName,
                $throughKeyName,
                $queryBuilder,
                $fetcher
            ): Relation {
                $innerClass = $fetcher->getClass();
                /** @var callable(TOuter):TKey */
                $outerKeySelector = AccessorCreators::createKeySelector($outerClass, $outerKeyName);
                /** @var callable(TInner):TKey */
                $innerKeySelector = AccessorCreators::createKeySelector($innerClass, $innerKeyName);
                /** @var callable(TInner):TKey */
                $throughKeySelector = AccessorCreators::createKeySelector($innerClass, $throughKeyName);
                /** @var callable(TOuter,?TKey):TOuter */
                $relationKeyAssignor = AccessorCreators::createKeyAssignor($outerClass, $relationKeyName);
                $resultSelector =
                    /**
                     * @param TOuter $lhs
                     * @param ?TInner $rhs
                     * @return TOuter
                     */
                    function(mixed $lhs, mixed $rhs) use ($relationKeyAssignor, $throughKeySelector): mixed {
                        $throughKeyName = $rhs !== null ? $throughKeySelector($rhs) : null;
                        return $relationKeyAssignor($lhs, $throughKeyName);
                    };
                /** @var LooseEqualityComparer<TKey> */
                $comparer = LooseEqualityComparer::getInstance();
                /** @var OneTo<TInner,TKey> */
                $relationStrategy = new OneTo(
                    $relationKeyName,
                    $tableName,
                    $outerKeyName,
                    $innerKeyName,
                    $queryBuilder,
                    $fetcher
                );
                /** @var OuterJoin<TOuter,TInner,TKey,TOuter> */
                $joinStrategy = new OuterJoin(
                    $outerKeySelector,
                    $innerKeySelector,
                    $resultSelector,
                    $comparer
                );
                return new Relation($outerClass, $relationStrategy, $joinStrategy);
            };
    }

    /**
     * @template TOuter
     * @template TInner
     * @template TKey
     * @param FetcherInterface<TInner> $fetcher
     * @return callable(?class-string):Relation<TOuter,TInner,TKey,TOuter>
     */
    public static function throughOneToMany(
        string $relationKeyName,
        string $tableName,
        string $outerKeyName,
        string $innerKeyName,
        string $throughKeyName,
        SelectBuilder $queryBuilder,
        FetcherInterface $fetcher
    ): callable {
        return
            /**
             * @param ?class-string $outerClass
             * @return Relation<TOuter,TInner,TKey,TOuter>
             */
            function(?string $outerClass) use (
                $relationKeyName,
                $tableName,
                $outerKeyName,
                $innerKeyName,
                $throughKeyName,
                $queryBuilder,
                $fetcher
            ): Relation {
                $innerClass = $fetcher->getClass();
                /** @var callable(TOuter):TKey */
                $outerKeySelector = AccessorCreators::createKeySelector($outerClass, $outerKeyName);
                /** @var callable(TInner):TKey */
                $innerKeySelector = AccessorCreators::createKeySelector($innerClass, $innerKeyName);
                /** @var callable(TInner):TKey */
                $throughKeySelector = AccessorCreators::createKeySelector($innerClass, $throughKeyName);
                /** @var callable(TOuter,TKey[]):TOuter */
                $relationKeyAssignor = AccessorCreators::createKeyAssignor($outerClass, $relationKeyName);
                $resultSelector =
                    /**
                     * @param TOuter $lhs
                     * @param TInner[] $rhs
                     * @return TOuter
                     */
                    function(mixed $lhs, mixed $rhs) use ($relationKeyAssignor, $throughKeySelector): mixed {
                        $throughKeys = array_map($throughKeySelector, $rhs);
                        return $relationKeyAssignor($lhs, $throughKeys);
                    };
                /** @var LooseEqualityComparer<TKey> */
                $comparer = LooseEqualityComparer::getInstance();
                /** @var OneTo<TInner,TKey> */
                $relationStrategy = new OneTo(
                    $relationKeyName,
                    $tableName,
                    $outerKeyName,
                    $innerKeyName,
                    $queryBuilder,
                    $fetcher
                );
                /** @var GroupJoin<TOuter,TInner,TKey,TOuter> */
                $joinStrategy = new GroupJoin(
                    $outerKeySelector,
                    $innerKeySelector,
                    $resultSelector,
                    $comparer
                );
                return new Relation($outerClass, $relationStrategy, $joinStrategy);
            };
    }

    /**
     * @template TOuter
     * @template TInner
     * @template TKey
     * @param FetcherInterface<TInner> $fetcher
     * @return callable(?class-string):Relation<TOuter,TInner,TKey,TOuter>
     */
    public static function lazyOneToOne(
        string $relationKeyName,
        string $tableName,
        string $outerKeyName,
        string $innerKeyName,
        SelectBuilder $queryBuilder,
        FetcherInterface $fetcher
    ): callable {
        return
            /**
             * @param ?class-string $outerClass
             * @return Relation<TOuter,TInner,TKey,TOuter>
             */
            function(?string $outerClass) use (
                $relationKeyName,
                $tableName,
                $outerKeyName,
                $innerKeyName,
                $queryBuilder,
                $fetcher
            ): Relation {
                $innerClass = $fetcher->getClass();
                /** @var callable(TOuter):TKey */
                $outerKeySelector = AccessorCreators::createKeySelector($outerClass, $outerKeyName);
                /** @var callable(TInner):TKey */
                $innerKeySelector = AccessorCreators::createKeySelector($innerClass, $innerKeyName);
                /** @var callable(TOuter,LazyValue<?TInner,mixed>):TOuter */
                $resultSelector = AccessorCreators::createKeyAssignor($outerClass, $relationKeyName);
                /** @var LooseEqualityComparer<TKey> */
                $comparer = LooseEqualityComparer::getInstance();
                /** @var OneTo<TInner,TKey> */
                $relationStrategy = new OneTo(
                    $relationKeyName,
                    $tableName,
                    $outerKeyName,
                    $innerKeyName,
                    $queryBuilder,
                    $fetcher
                );
                /** @var LazyOuterJoin<TOuter,TInner,TKey,TOuter> */
                $joinStrategy = new LazyOuterJoin(
                    $outerKeySelector,
                    $innerKeySelector,
                    $resultSelector,
                    $comparer
                );
                return new Relation($outerClass, $relationStrategy, $joinStrategy);
            };
    }

    /**
     * @template TOuter
     * @template TInner
     * @template TKey
     * @param FetcherInterface<TInner> $fetcher
     * @return callable(?class-string):Relation<TOuter,TInner,TKey,TOuter>
     */
    public static function lazyOneToMany(
        string $relationKeyName,
        string $tableName,
        string $outerKeyName,
        string $innerKeyName,
        SelectBuilder $queryBuilder,
        FetcherInterface $fetcher
    ): callable {
        return
            /**
             * @param ?class-string $outerClass
             * @return Relation<TOuter,TInner,TKey,TOuter>
             */
            function(?string $outerClass) use (
                $relationKeyName,
                $tableName,
                $outerKeyName,
                $innerKeyName,
                $queryBuilder,
                $fetcher
            ): Relation {
                $innerClass = $fetcher->getClass();
                /** @var callable(TOuter):TKey */
                $outerKeySelector = AccessorCreators::createKeySelector($outerClass, $outerKeyName);
                /** @var callable(TInner):TKey */
                $innerKeySelector = AccessorCreators::createKeySelector($innerClass, $innerKeyName);
                /** @var callable(TOuter,LazyCollection<TInner,TKey>):TOuter */
                $resultSelector = AccessorCreators::createKeyAssignor($outerClass, $relationKeyName);
                /** @var LooseEqualityComparer<TKey> */
                $comparer = LooseEqualityComparer::getInstance();
                /** @var OneTo<TInner,TKey> */
                $relationStrategy = new OneTo(
                    $relationKeyName,
                    $tableName,
                    $outerKeyName,
                    $innerKeyName,
                    $queryBuilder,
                    $fetcher
                );
                /** @var LazyGroupJoin<TOuter,TInner,TKey,TOuter> */
                $joinStrategy = new LazyGroupJoin(
                    $outerKeySelector,
                    $innerKeySelector,
                    $resultSelector,
                    $comparer
                );
                return new Relation($outerClass, $relationStrategy, $joinStrategy);
            };
    }

    /**
     * @template TOuter
     * @template TInner
     * @template TKey
     * @param FetcherInterface<TInner> $fetcher
     * @param callable(TKey):string $cacheKeySelector
     * @return callable(?class-string):Relation<TOuter,TInner,TKey,TOuter>
     */
    public static function cachedOneToOne(
        string $relationKeyName,
        string $tableName,
        string $outerKeyName,
        string $innerKeyName,
        SelectBuilder $queryBuilder,
        FetcherInterface $fetcher,
        CacheInterface $cache,
        callable $cacheKeySelector,
        ?int $cacheTtl = null
    ): callable {
        return
            /**
             * @param ?class-string $outerClass
             * @return Relation<TOuter,TInner,TKey,TOuter>
             */
            function(?string $outerClass) use (
                $relationKeyName,
                $tableName,
                $outerKeyName,
                $innerKeyName,
                $queryBuilder,
                $fetcher,
                $cache,
                $cacheKeySelector,
                $cacheTtl
            ): Relation {
                $innerClass = $fetcher->getClass();
                /** @var callable(TOuter):TKey */
                $outerKeySelector = AccessorCreators::createKeySelector($outerClass, $outerKeyName);
                /** @var callable(TInner):TKey */
                $innerKeySelector = AccessorCreators::createKeySelector($innerClass, $innerKeyName);
                /** @var callable(TOuter,?TInner):TOuter */
                $resultSelector = AccessorCreators::createKeyAssignor($outerClass, $relationKeyName);
                /** @var LooseEqualityComparer<TKey> */
                $comparer = LooseEqualityComparer::getInstance();
                /** @var Cached<TInner,TKey> */
                $relationStrategy = new Cached(
                    new OneTo(
                        $relationKeyName,
                        $tableName,
                        $outerKeyName,
                        $innerKeyName,
                        $queryBuilder,
                        $fetcher
                    ),
                    $cache,
                    $cacheKeySelector,
                    $cacheTtl
                );
                /** @var OuterJoin<TOuter,TInner,TKey,TOuter> */
                $joinStrategy = new OuterJoin(
                    $outerKeySelector,
                    $innerKeySelector,
                    $resultSelector,
                    $comparer
                );
                return new Relation($outerClass, $relationStrategy, $joinStrategy);
            };
    }

    /**
     * @template TOuter
     * @template TInner
     * @template TKey
     * @param ?class-string<TInner> $innerClass
     * @param TInner[] $innerElements
     * @return callable(?class-string):Relation<TOuter,TInner,TKey,TOuter>
     */
    public static function preloadedOneToOne(
        string $relationKeyName,
        string $outerKeyName,
        string $innerKeyName,
        ?string $innerClass,
        array $innerElements
    ): callable {
        return
            /**
             * @param ?class-string $outerClass
             * @return Relation<TOuter,TInner,TKey,TOuter>
             */
            function(?string $outerClass) use (
                $relationKeyName,
                $outerKeyName,
                $innerKeyName,
                $innerClass,
                $innerElements
            ): Relation {
                /** @var callable(TOuter):TKey */
                $outerKeySelector = AccessorCreators::createKeySelector($outerClass, $outerKeyName);
                /** @var callable(TInner):TKey */
                $innerKeySelector = AccessorCreators::createKeySelector($innerClass, $innerKeyName);
                /** @var callable(TOuter,?TInner):TOuter */
                $resultSelector = AccessorCreators::createKeyAssignor($outerClass, $relationKeyName);
                /** @var LooseEqualityComparer<TKey> */
                $comparer = LooseEqualityComparer::getInstance();
                /** @var Preloaded<TInner,TKey> */
                $relationStrategy = new Preloaded(
                    $relationKeyName,
                    $outerKeyName,
                    $innerKeyName,
                    $innerElements
                );
                /** @var OuterJoin<TOuter,TInner,TKey,TOuter> */
                $joinStrategy = new OuterJoin(
                    $outerKeySelector,
                    $innerKeySelector,
                    $resultSelector,
                    $comparer
                );
                return new Relation($outerClass, $relationStrategy, $joinStrategy);
            };
    }

    /**
     * @template TOuter
     * @template TInner
     * @template TKey
     * @param ?class-string<TInner> $innerClass
     * @param TInner[] $innerElements
     * @param ?class-string $collationClass
     * @return callable(?class-string):Relation<TOuter,TInner,TKey,TOuter>
     */
    public static function preloadedOneToMany(
        string $relationKeyName,
        string $outerKeyName,
        string $innerKeyName,
        ?string $innerClass,
        array $innerElements,
        ?string $collationClass = null
    ): callable {
        return
            /**
             * @param ?class-string $outerClass
             * @return RelationInterface<TOuter,TOuter>
             */
            function(?string $outerClass) use (
                $relationKeyName,
                $outerKeyName,
                $innerKeyName,
                $innerClass,
                $innerElements,
                $collationClass
            ): RelationInterface {
                /** @var callable(TOuter):TKey */
                $outerKeySelector = AccessorCreators::createKeySelector($outerClass, $outerKeyName);
                /** @var callable(TInner):TKey */
                $innerKeySelector = AccessorCreators::createKeySelector($innerClass, $innerKeyName);
                /** @var callable(TOuter,iterable<TInner>):TOuter */
                $resultSelector = AccessorCreators::createKeyAssignor($outerClass, $relationKeyName);
                if ($collationClass !== null) {
                    $resultSelector =
                        /**
                         * @param TOuter $lhs
                         * @param TInner[] $rhs
                         * @return TOuter
                         */
                        function(mixed $lhs, mixed $rhs) use ($collationClass, $resultSelector): mixed {
                            /** @var \Traversable<TInner> */
                            $rhs = new $collationClass($rhs);
                            return $resultSelector($lhs, $rhs);
                        };
                }
                /** @var LooseEqualityComparer<TKey> */
                $comparer = LooseEqualityComparer::getInstance();
                /** @var Preloaded<TInner,TKey> */
                $relationStrategy = new Preloaded(
                    $relationKeyName,
                    $outerKeyName,
                    $innerKeyName,
                    $innerElements
                );
                /** @var GroupJoin<TOuter,TInner,TKey,TOuter> */
                $joinStrategy = new GroupJoin(
                    $outerKeySelector,
                    $innerKeySelector,
                    $resultSelector,
                    $comparer
                );
                return new Relation($outerClass, $relationStrategy, $joinStrategy);
            };
    }

    /**
     * @template TOuter
     * @template TInner
     * @template TKey
     * @param FetcherInterface<TInner> $fetcher
     * @param ?class-string $collationClass
     * @return callable(?class-string):Relation<TOuter,TInner,TKey,TOuter>
     */
    public static function manyToMany(
        string $relationKeyName,
        string $oneToManyTableName,
        string $oneToManyOuterKeyName,
        string $oneToManyInnerKeyName,
        string $manyToOneTableName,
        string $manyToOneOuterKeyName,
        string $manyToOneInnerKeyName,
        SelectBuilder $queryBuilder,
        FetcherInterface $fetcher,
        ?string $collationClass = null
    ): callable {
        return
            /**
             * @param ?class-string $outerClass
             * @return Relation<TOuter,TInner,TKey,TOuter>
             */
            function(?string $outerClass) use (
                $relationKeyName,
                $oneToManyTableName,
                $oneToManyOuterKeyName,
                $oneToManyInnerKeyName,
                $manyToOneTableName,
                $manyToOneOuterKeyName,
                $manyToOneInnerKeyName,
                $queryBuilder,
                $fetcher,
                $collationClass
            ): Relation {
                $innerClass = $fetcher->getClass();
                $pivotKeyName = '__pivot_' . $oneToManyInnerKeyName;
                /** @var callable(TOuter):TKey */
                $outerKeySelector = AccessorCreators::createKeySelector($outerClass, $oneToManyOuterKeyName);
                /** @var callable(TInner):TKey */
                $innerKeySelector = AccessorCreators::createPivotKeySelector($innerClass, $pivotKeyName);
                /** @var callable(TOuter,iterable<TInner>):TOuter */
                $resultSelector = AccessorCreators::createKeyAssignor($outerClass, $relationKeyName);
                if ($collationClass !== null) {
                    $resultSelector =
                        /**
                         * @param TOuter $lhs
                         * @param TInner[] $rhs
                         * @return TOuter
                         */
                        function(mixed $lhs, mixed $rhs) use ($collationClass, $resultSelector): mixed {
                            /** @var iterable<TInner> */
                            $rhs = new $collationClass($rhs);
                            return $resultSelector($lhs, $rhs);
                        };
                }
                /** @var LooseEqualityComparer<TKey> */
                $comparer = LooseEqualityComparer::getInstance();
                /** @var Preloaded<TInner,TKey> */
                $relationStrategy = new ManyTo(
                    $relationKeyName,
                    $oneToManyTableName,
                    $oneToManyOuterKeyName,
                    $oneToManyInnerKeyName,
                    $manyToOneTableName,
                    $manyToOneOuterKeyName,
                    $manyToOneInnerKeyName,
                    $pivotKeyName,
                    $queryBuilder,
                    $fetcher
                );
                /** @var GroupJoin<TOuter,TInner,TKey,TOuter> */
                $joinStrategy = new GroupJoin(
                    $outerKeySelector,
                    $innerKeySelector,
                    $resultSelector,
                    $comparer
                );
                return new Relation($outerClass, $relationStrategy, $joinStrategy);
            };
    }

    /**
     * @template TOuter
     * @template TInner
     * @template TKey
     * @param FetcherInterface<TInner> $fetcher
     * @return callable(?class-string):RelationInterface<TOuter,TOuter>
     */
    public static function throughManyToMany(
        string $relationKeyName,
        string $oneToManyTableName,
        string $oneToManyOuterKeyName,
        string $oneToManyInnerKeyName,
        string $manyToOneTableName,
        string $manyToOneOuterKeyName,
        string $manyToOneInnerKeyName,
        string $throughKeyName,
        SelectBuilder $queryBuilder,
        FetcherInterface $fetcher
    ): callable {
        return
            /**
             * @param ?class-string $outerClass
             * @return RelationInterface<TOuter,TOuter>
             */
            function(?string $outerClass) use (
                $relationKeyName,
                $oneToManyTableName,
                $oneToManyOuterKeyName,
                $oneToManyInnerKeyName,
                $manyToOneTableName,
                $manyToOneOuterKeyName,
                $manyToOneInnerKeyName,
                $throughKeyName,
                $queryBuilder,
                $fetcher
            ): RelationInterface {
                $innerClass = $fetcher->getClass();
                $pivotKeyName = '__pivot_' . $oneToManyInnerKeyName;
                /** @var callable(TOuter):TKey */
                $outerKeySelector = AccessorCreators::createKeySelector($outerClass, $oneToManyOuterKeyName);
                /** @var callable(TInner):TKey */
                $innerKeySelector = AccessorCreators::createPivotKeySelector($innerClass, $pivotKeyName);
                /** @var callable(TInner):mixed */
                $throughKeySelector = AccessorCreators::createKeySelector($innerClass, $throughKeyName);
                /** @var callable(TOuter,mixed[]):TOuter */
                $throughKeysAssignor = AccessorCreators::createKeyAssignor($outerClass, $relationKeyName);
                $resultSelector =
                    /**
                     * @param TOuter $lhs
                     * @param TInner[] $rhs
                     * @return TOuter
                     */
                    function(mixed $lhs, mixed $rhs) use ($throughKeysAssignor, $throughKeySelector): mixed {
                        $throughKeys = array_map($throughKeySelector, $rhs);
                        return $throughKeysAssignor($lhs, $throughKeys);
                    };
                /** @var LooseEqualityComparer<TKey> */
                $comparer = LooseEqualityComparer::getInstance();
                /** @var ManyTo<TInner,TKey> */
                $relationStrategy = new ManyTo(
                    $relationKeyName,
                    $oneToManyTableName,
                    $oneToManyOuterKeyName,
                    $oneToManyInnerKeyName,
                    $manyToOneTableName,
                    $manyToOneOuterKeyName,
                    $manyToOneInnerKeyName,
                    $pivotKeyName,
                    $queryBuilder,
                    $fetcher
                );
                /** @var GroupJoin<TOuter,TInner,TKey,TOuter> */
                $joinStrategy = new GroupJoin(
                    $outerKeySelector,
                    $innerKeySelector,
                    $resultSelector,
                    $comparer
                );
                return new Relation($outerClass, $relationStrategy, $joinStrategy);
            };
    }

    /**
     * @template TOuter
     * @param array<string,callable(?class-string):RelationInterface<TOuter,TOuter>> $relationFactories
     * @return callable(?class-string):PolymorphicRelation<TOuter>
     */
    public static function polymorphic(string $morphKeyName, array $relationFactories): callable
    {
        return
            /**
             * @param ?class-string $outerClass
             * @return RelationInterface<TOuter,TOuter>
             */
            function(?string $outerClass) use (
                $morphKeyName,
                $relationFactories
            ): RelationInterface {
                /** @var array<string,RelationInterface<TOuter,TOuter>> */
                $relations = [];
                foreach ($relationFactories as $morphType => $relationFactory) {
                    $relations[$morphType] = $relationFactory($outerClass);
                }
                /** @var callable(TOuter):string */
                $morphKeySelector = AccessorCreators::createKeySelector($outerClass, $morphKeyName);
                /** @var PolymorphicRelation<TOuter> */
                return new PolymorphicRelation(
                    $outerClass,
                    $morphKeySelector,
                    $relations
                );
            };
    }

    /**
     * @codeCoverageIgnore
     */
    private function __construct()
    {
    }
}
