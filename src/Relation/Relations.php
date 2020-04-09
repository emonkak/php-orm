<?php

declare(strict_types=1);

namespace Emonkak\Orm\Relation;

use Emonkak\Enumerable\EqualityComparerInterface;  // @phan-suppress-current-line PhanUnreferencedUseNormal
use Emonkak\Enumerable\LooseEqualityComparer;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\Relation\JoinStrategy\GroupJoin;
use Emonkak\Orm\Relation\JoinStrategy\LazyGroupJoin;
use Emonkak\Orm\Relation\JoinStrategy\LazyOuterJoin;
use Emonkak\Orm\Relation\JoinStrategy\LazyCollection;  // @phan-suppress-current-line PhanUnreferencedUseNormal
use Emonkak\Orm\Relation\JoinStrategy\LazyValue;  // @phan-suppress-current-line PhanUnreferencedUseNormal
use Emonkak\Orm\Relation\JoinStrategy\OuterJoin;
use Emonkak\Orm\SelectBuilder;
use Psr\SimpleCache\CacheInterface;

final class Relations
{
    /**
     * @template TOuter
     * @template TInner
     * @template TKey of ?scalar
     * @psalm-param FetcherInterface<TInner> $fetcher
     * @psalm-return callable(?class-string<TOuter>):RelationInterface<TOuter,TOuter>
     */
    public static function oneToOne(
        string $relationKey,
        string $table,
        string $outerKey,
        string $innerKey,
        SelectBuilder $queryBuilder,
        FetcherInterface $fetcher
    ): callable {
        return
            /**
             * @psalm-param ?class-string<TOuter> $outerClass
             * @psalm-return RelationInterface<TOuter,TOuter>
             */
            function(?string $outerClass) use (
                $relationKey,
                $table,
                $outerKey,
                $innerKey,
                $queryBuilder,
                $fetcher
            ): RelationInterface {
                $innerClass = $fetcher->getClass();
                /** @psalm-var callable(TOuter):TKey */
                $outerKeySelector = AccessorCreators::createKeySelector($outerClass, $outerKey);
                /** @psalm-var callable(TInner):TKey */
                $innerKeySelector = AccessorCreators::createKeySelector($innerClass, $innerKey);
                /** @psalm-var callable(TOuter,?TInner):TOuter */
                $resultSelector = AccessorCreators::createKeyAssignee($outerClass, $relationKey);
                /** @psalm-var EqualityComparerInterface<TKey> */
                $comparer = LooseEqualityComparer::getInstance();
                return new Relation(
                    $outerClass,
                    new OneTo(
                        $relationKey,
                        $table,
                        $outerKey,
                        $innerKey,
                        $queryBuilder,
                        $fetcher
                    ),
                    new OuterJoin(
                        $outerKeySelector,
                        $innerKeySelector,
                        $resultSelector,
                        $comparer
                    )
                );
            };
    }

    /**
     * @template TOuter
     * @template TInner
     * @template TKey of ?scalar
     * @psalm-param FetcherInterface<TInner> $fetcher
     * @psalm-param ?class-string $collationClass
     * @psalm-return callable(?class-string<TOuter>):RelationInterface<TOuter,TOuter>
     */
    public static function oneToMany(
        string $relationKey,
        string $table,
        string $outerKey,
        string $innerKey,
        SelectBuilder $queryBuilder,
        FetcherInterface $fetcher,
        ?string $collationClass = null
    ): callable {
        return
            /**
             * @psalm-param ?class-string<TOuter> $outerClass
             * @psalm-return RelationInterface<TOuter,TOuter>
             */
            function(?string $outerClass) use (
                $relationKey,
                $table,
                $outerKey,
                $innerKey,
                $queryBuilder,
                $fetcher,
                $collationClass
            ): RelationInterface {
                $innerClass = $fetcher->getClass();
                /** @psalm-var callable(TOuter):TKey */
                $outerKeySelector = AccessorCreators::createKeySelector($outerClass, $outerKey);
                /** @psalm-var callable(TInner):TKey */
                $innerKeySelector = AccessorCreators::createKeySelector($innerClass, $innerKey);
                /** @psalm-var callable(TOuter,mixed):TOuter */
                $resultSelector = AccessorCreators::createKeyAssignee($outerClass, $relationKey);
                if ($collationClass !== null) {
                    $resultSelector =
                        /**
                         * @psalm-param TOuter $lhs
                         * @psalm-param TInner[] $rhs
                         * @psalm-return TOuter
                         */
                        function($lhs, $rhs) use ($collationClass, $resultSelector) {
                            return $resultSelector($lhs, new $collationClass($rhs));
                        };
                }
                /** @psalm-var EqualityComparerInterface<TKey> */
                $comparer = LooseEqualityComparer::getInstance();
                return new Relation(
                    $outerClass,
                    new OneTo(
                        $relationKey,
                        $table,
                        $outerKey,
                        $innerKey,
                        $queryBuilder,
                        $fetcher
                    ),
                    new GroupJoin(
                        $outerKeySelector,
                        $innerKeySelector,
                        $resultSelector,
                        $comparer
                    )
                );
            };
    }

    /**
     * @template TOuter
     * @template TInner
     * @template TKey of ?scalar
     * @template TThroughKey
     * @psalm-param FetcherInterface<TInner> $fetcher
     * @psalm-return callable(?class-string<TOuter>):RelationInterface<TOuter,TOuter>
     */
    public static function throughOneToOne(
        string $relationKey,
        string $table,
        string $outerKey,
        string $innerKey,
        string $throughKey,
        SelectBuilder $queryBuilder,
        FetcherInterface $fetcher
    ): callable {
        return
            /**
             * @psalm-param ?class-string<TOuter> $outerClass
             * @psalm-return RelationInterface<TOuter,TOuter>
             */
            function(?string $outerClass) use (
                $relationKey,
                $table,
                $outerKey,
                $innerKey,
                $throughKey,
                $queryBuilder,
                $fetcher
            ): RelationInterface {
                $innerClass = $fetcher->getClass();
                /** @psalm-var callable(TOuter):TKey */
                $outerKeySelector = AccessorCreators::createKeySelector($outerClass, $outerKey);
                /** @psalm-var callable(TInner):TKey */
                $innerKeySelector = AccessorCreators::createKeySelector($innerClass, $innerKey);
                /** @psalm-var callable(TInner):TThroughKey */
                $throughKeySelector = AccessorCreators::createKeySelector($innerClass, $throughKey);
                /** @psalm-var callable(TOuter,mixed):TOuter */
                $resultSelector = AccessorCreators::createKeyAssignee($outerClass, $relationKey);
                $resultSelector =
                    /**
                     * @psalm-param TOuter $lhs
                     * @psalm-param ?TInner $rhs
                     * @psalm-return TOuter
                     */
                    function($lhs, $rhs) use ($resultSelector, $throughKeySelector) {
                        $throughKey = $rhs !== null ? $throughKeySelector($rhs) : null;
                        return $resultSelector($lhs, $throughKey);
                    };
                /** @psalm-var EqualityComparerInterface<TKey> */
                $comparer = LooseEqualityComparer::getInstance();
                return new Relation(
                    $outerClass,
                    new OneTo(
                        $relationKey,
                        $table,
                        $outerKey,
                        $innerKey,
                        $queryBuilder,
                        $fetcher
                    ),
                    new OuterJoin(
                        $outerKeySelector,
                        $innerKeySelector,
                        $resultSelector,
                        $comparer
                    )
                );
            };
    }

    /**
     * @template TOuter
     * @template TInner
     * @template TKey of ?scalar
     * @template TThroughKey
     * @psalm-param FetcherInterface<TInner> $fetcher
     * @psalm-return callable(?class-string<TOuter>):RelationInterface<TOuter,TOuter>
     */
    public static function throughOneToMany(
        string $relationKey,
        string $table,
        string $outerKey,
        string $innerKey,
        string $throughKey,
        SelectBuilder $queryBuilder,
        FetcherInterface $fetcher
    ): callable {
        return
            /**
             * @psalm-param ?class-string<TOuter> $outerClass
             * @psalm-return RelationInterface<TOuter,TOuter>
             */
            function(?string $outerClass) use(
                $relationKey,
                $table,
                $outerKey,
                $innerKey,
                $throughKey,
                $queryBuilder,
                $fetcher
            ): RelationInterface {
                $innerClass = $fetcher->getClass();
                /** @psalm-var callable(TOuter):TKey */
                $outerKeySelector = AccessorCreators::createKeySelector($outerClass, $outerKey);
                /** @psalm-var callable(TInner):TKey */
                $innerKeySelector = AccessorCreators::createKeySelector($innerClass, $innerKey);
                /** @psalm-var callable(TInner):TThroughKey */
                $throughKeySelector = AccessorCreators::createKeySelector($innerClass, $throughKey);
                /** @psalm-var callable(TOuter,TThroughKey[]):TOuter */
                $resultSelector = AccessorCreators::createKeyAssignee($outerClass, $relationKey);
                $resultSelector =
                    /**
                     * @psalm-param TOuter $lhs
                     * @psalm-param TInner[] $rhs
                     * @psalm-return TOuter
                     */
                    function($lhs, $rhs) use ($resultSelector, $throughKeySelector) {
                        $throughKeys = array_map($throughKeySelector, $rhs);
                        return $resultSelector($lhs, $throughKeys);
                    };
                /** @psalm-var EqualityComparerInterface<TKey> */
                $comparer = LooseEqualityComparer::getInstance();
                return new Relation(
                    $outerClass,
                    new OneTo(
                        $relationKey,
                        $table,
                        $outerKey,
                        $innerKey,
                        $queryBuilder,
                        $fetcher
                    ),
                    new GroupJoin(
                        $outerKeySelector,
                        $innerKeySelector,
                        $resultSelector,
                        $comparer
                    )
                );
            };
    }

    /**
     * @template TOuter
     * @template TInner
     * @template TKey of ?scalar
     * @psalm-param FetcherInterface<TInner> $fetcher
     * @psalm-return callable(?class-string<TOuter>):RelationInterface<TOuter,TOuter>
     */
    public static function lazyOneToOne(
        string $relationKey,
        string $table,
        string $outerKey,
        string $innerKey,
        SelectBuilder $queryBuilder,
        FetcherInterface $fetcher
    ): callable {
        return
            /**
             * @psalm-param ?class-string<TOuter> $outerClass
             * @psalm-return RelationInterface<TOuter,TOuter>
             */
            function(?string $outerClass) use (
                $relationKey,
                $table,
                $outerKey,
                $innerKey,
                $queryBuilder,
                $fetcher
            ): RelationInterface {
                $innerClass = $fetcher->getClass();
                /** @psalm-var callable(TOuter):TKey */
                $outerKeySelector = AccessorCreators::createKeySelector($outerClass, $outerKey);
                /** @psalm-var callable(TInner):TKey */
                $innerKeySelector = AccessorCreators::createKeySelector($innerClass, $innerKey);
                /** @psalm-var callable(TOuter,LazyValue<?TInner,TKey>):TOuter */
                $resultSelector = AccessorCreators::createKeyAssignee($outerClass, $relationKey);
                /** @psalm-var EqualityComparerInterface<TKey> */
                $comparer = LooseEqualityComparer::getInstance();
                return new Relation(
                    $outerClass,
                    new OneTo(
                        $relationKey,
                        $table,
                        $outerKey,
                        $innerKey,
                        $queryBuilder,
                        $fetcher
                    ),
                    new LazyOuterJoin(
                        $outerKeySelector,
                        $innerKeySelector,
                        $resultSelector,
                        $comparer
                    )
                );
            };
    }

    /**
     * @template TOuter
     * @template TInner
     * @template TKey of ?scalar
     * @psalm-param FetcherInterface<TInner> $fetcher
     * @psalm-return callable(?class-string<TOuter>):RelationInterface<TOuter,TOuter>
     */
    public static function lazyOneToMany(
        string $relationKey,
        string $table,
        string $outerKey,
        string $innerKey,
        SelectBuilder $queryBuilder,
        FetcherInterface $fetcher
    ): callable {
        return
            /**
             * @psalm-param ?class-string<TOuter> $outerClass
             * @psalm-return RelationInterface<TOuter,TOuter>
             */
            function(?string $outerClass) use (
                $relationKey,
                $table,
                $outerKey,
                $innerKey,
                $queryBuilder,
                $fetcher
            ): RelationInterface {
                $innerClass = $fetcher->getClass();
                /** @psalm-var callable(TOuter):TKey */
                $outerKeySelector = AccessorCreators::createKeySelector($outerClass, $outerKey);
                /** @psalm-var callable(TInner):TKey */
                $innerKeySelector = AccessorCreators::createKeySelector($innerClass, $innerKey);
                /** @psalm-var callable(TOuter,LazyCollection<TInner,TKey>):TOuter */
                $resultSelector = AccessorCreators::createKeyAssignee($outerClass, $relationKey);
                /** @psalm-var EqualityComparerInterface<TKey> */
                $comparer = LooseEqualityComparer::getInstance();
                return new Relation(
                    $outerClass,
                    new OneTo(
                        $relationKey,
                        $table,
                        $outerKey,
                        $innerKey,
                        $queryBuilder,
                        $fetcher
                    ),
                    new LazyGroupJoin(
                        $outerKeySelector,
                        $innerKeySelector,
                        $resultSelector,
                        $comparer
                    )
                );
            };
    }

    /**
     * @template TOuter
     * @template TInner
     * @template TKey of ?scalar
     * @psalm-param FetcherInterface<TInner> $fetcher
     * @psalm-param callable(TKey):string $cacheKeySelector
     * @psalm-return callable(?class-string<TOuter>):RelationInterface<TOuter,TOuter>
     */
    public static function cachedOneToOne(
        string $relationKey,
        string $table,
        string $outerKey,
        string $innerKey,
        SelectBuilder $queryBuilder,
        FetcherInterface $fetcher,
        CacheInterface $cache,
        callable $cacheKeySelector,
        ?int $cacheTtl = null
    ): callable {
        return
            /**
             * @psalm-param ?class-string<TOuter> $outerClass
             * @psalm-return RelationInterface<TOuter,TOuter>
             */
            function(?string $outerClass) use (
                $relationKey,
                $table,
                $outerKey,
                $innerKey,
                $queryBuilder,
                $fetcher,
                $cache,
                $cacheKeySelector,
                $cacheTtl
            ): RelationInterface {
                $innerClass = $fetcher->getClass();
                /** @psalm-var callable(TOuter):TKey */
                $outerKeySelector = AccessorCreators::createKeySelector($outerClass, $outerKey);
                /** @psalm-var callable(TInner):TKey */
                $innerKeySelector = AccessorCreators::createKeySelector($innerClass, $innerKey);
                /** @psalm-var callable(TOuter,?TInner):TOuter */
                $resultSelector = AccessorCreators::createKeyAssignee($outerClass, $relationKey);
                /** @psalm-var EqualityComparerInterface<TKey> */
                $comparer = LooseEqualityComparer::getInstance();
                return new Relation(
                    $outerClass,
                    new Cached(
                        new OneTo(
                            $relationKey,
                            $table,
                            $outerKey,
                            $innerKey,
                            $queryBuilder,
                            $fetcher
                        ),
                        $cache,
                        $cacheKeySelector,
                        $cacheTtl
                    ),
                    new OuterJoin(
                        $outerKeySelector,
                        $innerKeySelector,
                        $resultSelector,
                        $comparer
                    )
                );
            };
    }

    /**
     * @template TOuter
     * @template TInner
     * @template TKey of ?scalar
     * @psalm-param ?class-string<TInner> $innerClass
     * @psalm-param TInner[] $innerElements
     * @psalm-return callable(?class-string<TOuter>):RelationInterface<TOuter,TOuter>
     */
    public static function preloadedOneToOne(
        string $relationKey,
        string $outerKey,
        string $innerKey,
        ?string $innerClass,
        array $innerElements
    ): callable {
        return
            /**
             * @psalm-param ?class-string<TOuter> $outerClass
             * @psalm-return RelationInterface<TOuter,TOuter>
             */
            function(?string $outerClass) use (
                $relationKey,
                $outerKey,
                $innerKey,
                $innerClass,
                $innerElements
            ): RelationInterface {
                /** @psalm-var callable(TOuter):TKey */
                $outerKeySelector = AccessorCreators::createKeySelector($outerClass, $outerKey);
                /** @psalm-var callable(TInner):TKey */
                $innerKeySelector = AccessorCreators::createKeySelector($innerClass, $innerKey);
                /** @psalm-var callable(TOuter,?TInner):TOuter */
                $resultSelector = AccessorCreators::createKeyAssignee($outerClass, $relationKey);
                /** @psalm-var EqualityComparerInterface<TKey> */
                $comparer = LooseEqualityComparer::getInstance();
                return new Relation(
                    $outerClass,
                    new Preloaded(
                        $relationKey,
                        $outerKey,
                        $innerKey,
                        $innerElements
                    ),
                    new OuterJoin(
                        $outerKeySelector,
                        $innerKeySelector,
                        $resultSelector,
                        $comparer
                    )
                );
            };
    }

    /**
     * @template TOuter
     * @template TInner
     * @template TKey of ?scalar
     * @psalm-param ?class-string<TInner> $innerClass
     * @psalm-param TInner[] $innerElements
     * @psalm-param ?class-string $collationClass
     * @psalm-return callable(?class-string<TOuter>):RelationInterface<TOuter,TOuter>
     */
    public static function preloadedOneToMany(
        string $relationKey,
        string $outerKey,
        string $innerKey,
        ?string $innerClass,
        array $innerElements,
        ?string $collationClass = null
    ): callable {
        return
            /**
             * @psalm-param ?class-string<TOuter> $outerClass
             * @psalm-return RelationInterface<TOuter,TOuter>
             */
            function(?string $outerClass) use (
                $relationKey,
                $outerKey,
                $innerKey,
                $innerClass,
                $innerElements,
                $collationClass
            ): RelationInterface {
                /** @psalm-var callable(TOuter):TKey */
                $outerKeySelector = AccessorCreators::createKeySelector($outerClass, $outerKey);
                /** @psalm-var callable(TInner):TKey */
                $innerKeySelector = AccessorCreators::createKeySelector($innerClass, $innerKey);
                /** @psalm-var callable(TOuter,mixed):TOuter */
                $resultSelector = AccessorCreators::createKeyAssignee($outerClass, $relationKey);
                if ($collationClass !== null) {
                    $resultSelector =
                        /**
                         * @psalm-param TOuter $lhs
                         * @psalm-param TInner[] $rhs
                         * @psalm-return TOuter
                         */
                        function($lhs, $rhs) use ($collationClass, $resultSelector) {
                            return $resultSelector($lhs, new $collationClass($rhs));
                        };
                }
                /** @psalm-var EqualityComparerInterface<TKey> */
                $comparer = LooseEqualityComparer::getInstance();
                return new Relation(
                    $outerClass,
                    new Preloaded(
                        $relationKey,
                        $outerKey,
                        $innerKey,
                        $innerElements
                    ),
                    new GroupJoin(
                        $outerKeySelector,
                        $innerKeySelector,
                        $resultSelector,
                        $comparer
                    )
                );
            };
    }

    /**
     * @template TOuter
     * @template TInner
     * @template TKey of ?scalar
     * @psalm-param FetcherInterface<TInner> $fetcher
     * @psalm-param ?class-string $collationClass
     * @psalm-return callable(?class-string<TOuter>):RelationInterface<TOuter,TOuter>
     */
    public static function manyToMany(
        string $relationKey,
        string $oneToManyTable,
        string $oneToManyOuterKey,
        string $oneToManyInnerKey,
        string $manyToOneTable,
        string $manyToOneOuterKey,
        string $manyToOneInnerKey,
        SelectBuilder $queryBuilder,
        FetcherInterface $fetcher,
        ?string $collationClass = null
    ): callable {
        return
            /**
             * @psalm-param ?class-string<TOuter> $outerClass
             * @psalm-return RelationInterface<TOuter,TOuter>
             */
            function(?string $outerClass) use (
                $relationKey,
                $oneToManyTable,
                $oneToManyOuterKey,
                $oneToManyInnerKey,
                $manyToOneTable,
                $manyToOneOuterKey,
                $manyToOneInnerKey,
                $queryBuilder,
                $fetcher,
                $collationClass
            ): RelationInterface {
                $innerClass = $fetcher->getClass();
                $pivotKey = '__pivot_' . $oneToManyInnerKey;
                /** @psalm-var callable(TOuter):TKey */
                $outerKeySelector = AccessorCreators::createKeySelector($outerClass, $oneToManyOuterKey);
                /** @psalm-var callable(TInner):TKey */
                $innerKeySelector = AccessorCreators::createPivotKeySelector($innerClass, $pivotKey);
                /** @psalm-var callable(TOuter,mixed):TOuter */
                $resultSelector = AccessorCreators::createKeyAssignee($outerClass, $relationKey);
                if ($collationClass !== null) {
                    $resultSelector =
                        /**
                         * @psalm-param TOuter $lhs
                         * @psalm-param TInner[] $rhs
                         * @psalm-return TOuter
                         */
                        function($lhs, $rhs) use ($collationClass, $resultSelector) {
                            return $resultSelector($lhs, new $collationClass($rhs));
                        };
                }
                /** @psalm-var EqualityComparerInterface<TKey> */
                $comparer = LooseEqualityComparer::getInstance();
                return new Relation(
                    $outerClass,
                    new ManyTo(
                        $relationKey,
                        $oneToManyTable,
                        $oneToManyOuterKey,
                        $oneToManyInnerKey,
                        $manyToOneTable,
                        $manyToOneOuterKey,
                        $manyToOneInnerKey,
                        $pivotKey,
                        $queryBuilder,
                        $fetcher
                    ),
                    new GroupJoin(
                        $outerKeySelector,
                        $innerKeySelector,
                        $resultSelector,
                        $comparer
                    )
                );
            };
    }

    /**
     * @template TOuter
     * @template TInner
     * @template TKey of ?scalar
     * @template TThroughKey
     * @psalm-param FetcherInterface<TInner> $fetcher
     * @psalm-return callable(?class-string<TOuter>):RelationInterface<TOuter,TOuter>
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
        SelectBuilder $queryBuilder,
        FetcherInterface $fetcher
    ): callable {
        return
            /**
             * @psalm-param ?class-string<TOuter> $outerClass
             * @psalm-return RelationInterface<TOuter,TOuter>
             */
            function(?string $outerClass) use (
                $relationKey,
                $oneToManyTable,
                $oneToManyOuterKey,
                $oneToManyInnerKey,
                $manyToOneTable,
                $manyToOneOuterKey,
                $manyToOneInnerKey,
                $throughKey,
                $queryBuilder,
                $fetcher
            ): RelationInterface {
                $innerClass = $fetcher->getClass();
                $pivotKey = '__pivot_' . $oneToManyInnerKey;
                /** @psalm-var callable(TOuter):TKey */
                $outerKeySelector = AccessorCreators::createKeySelector($outerClass, $oneToManyOuterKey);
                /** @psalm-var callable(TInner):TKey */
                $innerKeySelector = AccessorCreators::createPivotKeySelector($innerClass, $pivotKey);
                /** @psalm-var callable(TInner):TThroughKey */
                $throughKeySelector = AccessorCreators::createKeySelector($innerClass, $throughKey);
                /** @psalm-var callable(TOuter,TThroughKey[]):TOuter */
                $resultSelector = AccessorCreators::createKeyAssignee($outerClass, $relationKey);
                $resultSelector =
                    /**
                     * @psalm-param TOuter $lhs
                     * @psalm-param TInner[] $rhs
                     * @psalm-return TOuter
                     */
                    function($lhs, $rhs) use ($resultSelector, $throughKeySelector) {
                        $throughKeys = array_map($throughKeySelector, $rhs);
                        return $resultSelector($lhs, $throughKeys);
                    };
                /** @psalm-var EqualityComparerInterface<TKey> */
                $comparer = LooseEqualityComparer::getInstance();
                return new Relation(
                    $outerClass,
                    new ManyTo(
                        $relationKey,
                        $oneToManyTable,
                        $oneToManyOuterKey,
                        $oneToManyInnerKey,
                        $manyToOneTable,
                        $manyToOneOuterKey,
                        $manyToOneInnerKey,
                        $pivotKey,
                        $queryBuilder,
                        $fetcher
                    ),
                    new GroupJoin(
                        $outerKeySelector,
                        $innerKeySelector,
                        $resultSelector,
                        $comparer
                    )
                );
            };
    }

    /**
     * @template TOuter
     * @psalm-param array<string,callable(?class-string<TOuter>):RelationInterface<TOuter,TOuter>> $relationFactories
     * @psalm-return callable(?class-string<TOuter>):RelationInterface<TOuter,TOuter>
     */
    public static function polymorphic(string $morphKey, array $relationFactories): callable
    {
        return
            /**
             * @psalm-param ?class-string<TOuter> $outerClass
             * @psalm-return RelationInterface<TOuter,TOuter>
             */
            function(?string $outerClass) use (
                $morphKey,
                $relationFactories
            ): RelationInterface {
                /** @psalm-var array<string,RelationInterface<TOuter,TOuter>> */
                $relations = [];
                foreach ($relationFactories as $morphType => $relationFactory) {
                    $relations[$morphType] = $relationFactory($outerClass);
                }
                /** @psalm-var callable(?class-string<TOuter>):string */
                $morphKeySelector = AccessorCreators::createKeySelector($outerClass, $morphKey);
                /** @psalm-var RelationInterface<TOuter,TOuter> */
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
