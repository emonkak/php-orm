<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\Relation;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\Grammar\GrammarInterface;
use Emonkak\Orm\Relation\Cached;
use Emonkak\Orm\Relation\CachedRelationStrategy;
use Emonkak\Orm\Relation\JoinStrategy\GroupJoin;
use Emonkak\Orm\Relation\JoinStrategy\LazyGroupJoin;
use Emonkak\Orm\Relation\JoinStrategy\LazyOuterJoin;
use Emonkak\Orm\Relation\JoinStrategy\LazyValue;
use Emonkak\Orm\Relation\JoinStrategy\OuterJoin;
use Emonkak\Orm\Relation\JoinStrategy\ThroughGroupJoin;
use Emonkak\Orm\Relation\JoinStrategy\ThroughOuterJoin;
use Emonkak\Orm\Relation\ManyTo;
use Emonkak\Orm\Relation\OneTo;
use Emonkak\Orm\Relation\PolymorphicRelation;
use Emonkak\Orm\Relation\Preloaded;
use Emonkak\Orm\Relation\Relation;
use Emonkak\Orm\Relation\RelationInterface;
use Emonkak\Orm\Relation\RelationStrategyInterface;
use Emonkak\Orm\Relation\Relations;
use Emonkak\Orm\SelectBuilder;
use Emonkak\Orm\Tests\Fixtures\Model;
use Emonkak\Orm\Tests\Fixtures\Spy;
use PHPUnit\Framework\TestCase;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use Psr\SimpleCache\CacheInterface;

/**
 * @covers Emonkak\Orm\Relation\Relations
 */
class RelationsTest extends TestCase
{
    public function testOneToOne(): void
    {
        $outerClass = Model::class;
        $innerClass = Model::class;

        $relationKey = 'relation_key';
        $table = 'table';
        $outerKey = 'outer_key';
        $innerKey = 'inner_key';

        $pdo = $this->createMock(PDOInterface::class);
        $fetcher = $this->createMock(FetcherInterface::class);
        $fetcher
            ->expects($this->once())
            ->method('getClass')
            ->willReturn($innerClass);
        $grammar = $this->createMock(GrammarInterface::class);
        $queryBuilder = new SelectBuilder($grammar);
        $unions = [
            'union' => new SelectBuilder($grammar)
        ];

        $relation = Relations::oneToOne(
            $relationKey,
            $table,
            $outerKey,
            $innerKey,
            $pdo,
            $fetcher,
            $queryBuilder,
            $unions
        )($outerClass);
        $relationStrategy = $relation->getRelationStrategy();
        $joinStrategy = $relation->getJoinStrategy();

        $this->assertInstanceOf(Relation::class, $relation);
        $this->assertSame($outerClass, $relation->getResultClass());

        $this->assertInstanceOf(OneTo::class, $relationStrategy);
        $this->assertSame($relationKey, $relationStrategy->getRelationKey());
        $this->assertSame($table, $relationStrategy->getTable());
        $this->assertSame($outerKey, $relationStrategy->getOuterKey());
        $this->assertSame($innerKey, $relationStrategy->getInnerKey());
        $this->assertSame($pdo, $relationStrategy->getPdo());
        $this->assertSame($fetcher, $relationStrategy->getFetcher());
        $this->assertSame($queryBuilder, $relationStrategy->getQueryBuilder());
        $this->assertSame($unions, $relationStrategy->getUnions());

        $this->assertInstanceOf(OuterJoin::class, $joinStrategy);
        $this->assertSame(123, ($joinStrategy->getOuterKeySelector())(new Model(['outer_key' => 123])));
        $this->assertSame(456, ($joinStrategy->getInnerKeySelector())(new Model(['inner_key' => 456])));
        $this->assertEquals(
            new Model(['outer_key' => 123, 'relation_key' => new Model(['inner_key' => 456])]),
            ($joinStrategy->getResultSelector())(
                new Model(['outer_key' => 123]),
                new Model(['inner_key' => 456])
            )
        );
    }

    public function testOneToMany(): void
    {
        $outerClass = Model::class;
        $innerClass = Model::class;

        $relationKey = 'relation_key';
        $table = 'table';
        $outerKey = 'outer_key';
        $innerKey = 'inner_key';

        $pdo = $this->createMock(PDOInterface::class);
        $fetcher = $this->createMock(FetcherInterface::class);
        $fetcher
            ->expects($this->once())
            ->method('getClass')
            ->willReturn($innerClass);
        $grammar = $this->createMock(GrammarInterface::class);
        $queryBuilder = new SelectBuilder($grammar);
        $unions = [
            'union' => new SelectBuilder($grammar)
        ];

        $relation = Relations::oneToMany(
            $relationKey,
            $table,
            $outerKey,
            $innerKey,
            $pdo,
            $fetcher,
            $queryBuilder,
            $unions
        )($outerClass);
        $relationStrategy = $relation->getRelationStrategy();
        $joinStrategy = $relation->getJoinStrategy();

        $this->assertInstanceOf(Relation::class, $relation);
        $this->assertSame($outerClass, $relation->getResultClass());

        $this->assertInstanceOf(OneTo::class, $relationStrategy);
        $this->assertSame($relationKey, $relationStrategy->getRelationKey());
        $this->assertSame($table, $relationStrategy->getTable());
        $this->assertSame($outerKey, $relationStrategy->getOuterKey());
        $this->assertSame($innerKey, $relationStrategy->getInnerKey());
        $this->assertSame($pdo, $relationStrategy->getPdo());
        $this->assertSame($fetcher, $relationStrategy->getFetcher());
        $this->assertSame($queryBuilder, $relationStrategy->getQueryBuilder());
        $this->assertSame($unions, $relationStrategy->getUnions());

        $this->assertInstanceOf(GroupJoin::class, $joinStrategy);
        $this->assertSame(123, ($joinStrategy->getOuterKeySelector())(new Model(['outer_key' => 123])));
        $this->assertSame(456, ($joinStrategy->getInnerKeySelector())(new Model(['inner_key' => 456])));
        $this->assertEquals(
            new Model(['outer_key' => 123, 'relation_key' => [new Model(['inner_key' => 456]), new Model(['inner_key' => 789])]]),
            ($joinStrategy->getResultSelector())(
                new Model(['outer_key' => 123]),
                [new Model(['inner_key' => 456]), new Model(['inner_key' => 789])]
            )
        );
    }

    public function testThroughOneToOne(): void
    {
        $outerClass = Model::class;
        $innerClass = Model::class;

        $relationKey = 'relation_key';
        $table = 'table';
        $outerKey = 'outer_key';
        $innerKey = 'inner_key';
        $throughKey = 'through_key';

        $pdo = $this->createMock(PDOInterface::class);
        $fetcher = $this->createMock(FetcherInterface::class);
        $fetcher
            ->expects($this->once())
            ->method('getClass')
            ->willReturn($innerClass);
        $grammar = $this->createMock(GrammarInterface::class);
        $queryBuilder = new SelectBuilder($grammar);
        $unions = [
            'union' => new SelectBuilder($grammar)
        ];

        $relation = Relations::throughOneToOne(
            $relationKey,
            $table,
            $outerKey,
            $innerKey,
            $throughKey,
            $pdo,
            $fetcher,
            $queryBuilder,
            $unions
        )($outerClass);
        $relationStrategy = $relation->getRelationStrategy();
        $joinStrategy = $relation->getJoinStrategy();

        $this->assertInstanceOf(Relation::class, $relation);
        $this->assertSame($outerClass, $relation->getResultClass());

        $this->assertInstanceOf(OneTo::class, $relationStrategy);
        $this->assertSame($relationKey, $relationStrategy->getRelationKey());
        $this->assertSame($table, $relationStrategy->getTable());
        $this->assertSame($outerKey, $relationStrategy->getOuterKey());
        $this->assertSame($innerKey, $relationStrategy->getInnerKey());
        $this->assertSame($pdo, $relationStrategy->getPdo());
        $this->assertSame($fetcher, $relationStrategy->getFetcher());
        $this->assertSame($queryBuilder, $relationStrategy->getQueryBuilder());
        $this->assertSame($unions, $relationStrategy->getUnions());

        $this->assertInstanceOf(ThroughOuterJoin::class, $joinStrategy);
        $this->assertSame(123, ($joinStrategy->getOuterKeySelector())(new Model(['outer_key' => 123])));
        $this->assertSame(456, ($joinStrategy->getInnerKeySelector())(new Model(['inner_key' => 456])));
        $this->assertSame(789, ($joinStrategy->getThroughKeySelector())(new Model(['through_key' => 789])));
        $this->assertEquals(
            new Model(['outer_key' => 123, 'relation_key' => new Model(['inner_key' => 456])]),
            ($joinStrategy->getResultSelector())(
                new Model(['outer_key' => 123]),
                new Model(['inner_key' => 456])
            )
        );
    }

    public function testThroughOneToMany(): void
    {
        $outerClass = Model::class;
        $innerClass = Model::class;

        $relationKey = 'relation_key';
        $table = 'table';
        $outerKey = 'outer_key';
        $innerKey = 'inner_key';
        $throughKey = 'through_key';

        $pdo = $this->createMock(PDOInterface::class);
        $fetcher = $this->createMock(FetcherInterface::class);
        $fetcher
            ->expects($this->once())
            ->method('getClass')
            ->willReturn($innerClass);
        $grammar = $this->createMock(GrammarInterface::class);
        $queryBuilder = new SelectBuilder($grammar);
        $unions = [
            'union' => new SelectBuilder($grammar)
        ];

        $relation = Relations::throughOneToMany(
            $relationKey,
            $table,
            $outerKey,
            $innerKey,
            $throughKey,
            $pdo,
            $fetcher,
            $queryBuilder,
            $unions
        )($outerClass);
        $relationStrategy = $relation->getRelationStrategy();
        $joinStrategy = $relation->getJoinStrategy();

        $this->assertInstanceOf(Relation::class, $relation);
        $this->assertSame($outerClass, $relation->getResultClass());

        $this->assertInstanceOf(OneTo::class, $relationStrategy);
        $this->assertSame($relationKey, $relationStrategy->getRelationKey());
        $this->assertSame($table, $relationStrategy->getTable());
        $this->assertSame($outerKey, $relationStrategy->getOuterKey());
        $this->assertSame($innerKey, $relationStrategy->getInnerKey());
        $this->assertSame($pdo, $relationStrategy->getPdo());
        $this->assertSame($fetcher, $relationStrategy->getFetcher());
        $this->assertSame($queryBuilder, $relationStrategy->getQueryBuilder());
        $this->assertSame($unions, $relationStrategy->getUnions());

        $this->assertInstanceOf(ThroughGroupJoin::class, $joinStrategy);
        $this->assertSame(123, ($joinStrategy->getOuterKeySelector())(new Model(['outer_key' => 123])));
        $this->assertSame(456, ($joinStrategy->getInnerKeySelector())(new Model(['inner_key' => 456])));
        $this->assertSame(789, ($joinStrategy->getThroughKeySelector())(new Model(['through_key' => 789])));
        $this->assertEquals(
            new Model(['outer_key' => 123, 'relation_key' => [new Model(['inner_key' => 456]), new Model(['inner_key' => 789])]]),
            ($joinStrategy->getResultSelector())(
                new Model(['outer_key' => 123]),
                [new Model(['inner_key' => 456]), new Model(['inner_key' => 789])]
            )
        );
    }

    public function testLazyOneToOne(): void
    {
        $outerClass = Model::class;
        $innerClass = Model::class;

        $relationKey = 'relation_key';
        $table = 'table';
        $outerKey = 'outer_key';
        $innerKey = 'inner_key';

        $pdo = $this->createMock(PDOInterface::class);
        $fetcher = $this->createMock(FetcherInterface::class);
        $fetcher
            ->expects($this->once())
            ->method('getClass')
            ->willReturn($innerClass);
        $grammar = $this->createMock(GrammarInterface::class);
        $queryBuilder = new SelectBuilder($grammar);
        $unions = [
            'union' => new SelectBuilder($grammar)
        ];

        $proxyFactory = new LazyLoadingValueHolderFactory();

        $relation = Relations::lazyOneToOne(
            $relationKey,
            $table,
            $outerKey,
            $innerKey,
            $pdo,
            $fetcher,
            $queryBuilder,
            $unions,
            $proxyFactory
        )($outerClass);
        $relationStrategy = $relation->getRelationStrategy();
        $joinStrategy = $relation->getJoinStrategy();

        $this->assertInstanceOf(Relation::class, $relation);
        $this->assertSame($outerClass, $relation->getResultClass());

        $this->assertInstanceOf(OneTo::class, $relationStrategy);
        $this->assertSame($relationKey, $relationStrategy->getRelationKey());
        $this->assertSame($table, $relationStrategy->getTable());
        $this->assertSame($outerKey, $relationStrategy->getOuterKey());
        $this->assertSame($innerKey, $relationStrategy->getInnerKey());
        $this->assertSame($pdo, $relationStrategy->getPdo());
        $this->assertSame($fetcher, $relationStrategy->getFetcher());
        $this->assertSame($queryBuilder, $relationStrategy->getQueryBuilder());
        $this->assertSame($unions, $relationStrategy->getUnions());

        $this->assertInstanceOf(LazyOuterJoin::class, $joinStrategy);
        $this->assertSame(123, ($joinStrategy->getOuterKeySelector())(new Model(['outer_key' => 123])));
        $this->assertSame(456, ($joinStrategy->getInnerKeySelector())(new Model(['inner_key' => 456])));
        $this->assertEquals(
            new Model(['outer_key' => 123, 'relation_key' => new Model(['inner_key' => 456])]),
            ($joinStrategy->getResultSelector())(
                new Model(['outer_key' => 123]),
                new Model(['inner_key' => 456])
            )
        );
        $this->assertSame($proxyFactory, $joinStrategy->getProxyFactory());
    }

    public function testLazyOneToMany(): void
    {
        $outerClass = Model::class;
        $innerClass = Model::class;

        $relationKey = 'relation_key';
        $table = 'table';
        $outerKey = 'outer_key';
        $innerKey = 'inner_key';

        $pdo = $this->createMock(PDOInterface::class);
        $fetcher = $this->createMock(FetcherInterface::class);
        $fetcher
            ->expects($this->once())
            ->method('getClass')
            ->willReturn($innerClass);
        $grammar = $this->createMock(GrammarInterface::class);
        $queryBuilder = new SelectBuilder($grammar);
        $unions = [
            'union' => new SelectBuilder($grammar)
        ];

        $proxyFactory = new LazyLoadingValueHolderFactory();

        $relation = Relations::lazyOneToMany(
            $relationKey,
            $table,
            $outerKey,
            $innerKey,
            $pdo,
            $fetcher,
            $queryBuilder,
            $unions,
            $proxyFactory
        )($outerClass);
        $relationStrategy = $relation->getRelationStrategy();
        $joinStrategy = $relation->getJoinStrategy();

        $this->assertInstanceOf(Relation::class, $relation);
        $this->assertSame($outerClass, $relation->getResultClass());

        $this->assertInstanceOf(OneTo::class, $relationStrategy);
        $this->assertSame($relationKey, $relationStrategy->getRelationKey());
        $this->assertSame($table, $relationStrategy->getTable());
        $this->assertSame($outerKey, $relationStrategy->getOuterKey());
        $this->assertSame($innerKey, $relationStrategy->getInnerKey());
        $this->assertSame($pdo, $relationStrategy->getPdo());
        $this->assertSame($fetcher, $relationStrategy->getFetcher());
        $this->assertSame($queryBuilder, $relationStrategy->getQueryBuilder());
        $this->assertSame($unions, $relationStrategy->getUnions());

        $this->assertInstanceOf(LazyGroupJoin::class, $joinStrategy);
        $this->assertSame(123, ($joinStrategy->getOuterKeySelector())(new Model(['outer_key' => 123])));
        $this->assertSame(456, ($joinStrategy->getInnerKeySelector())(new Model(['inner_key' => 456])));
        $this->assertEquals(
            new Model(['outer_key' => 123, 'relation_key' => [new Model(['inner_key' => 456]), new Model(['inner_key' => 789])]]),
            ($joinStrategy->getResultSelector())(
                new Model(['outer_key' => 123]),
                [new Model(['inner_key' => 456]), new Model(['inner_key' => 789])]
            )
        );
        $this->assertSame($proxyFactory, $joinStrategy->getProxyFactory());
    }

    public function testCachedOneToOne(): void
    {
        $outerClass = Model::class;
        $innerClass = Model::class;

        $relationKey = 'relation_key';
        $outerKey = 'outer_key';
        $innerKey = 'inner_key';
        $table = 'table';

        $pdo = $this->createMock(PDOInterface::class);
        $fetcher = $this->createMock(FetcherInterface::class);
        $fetcher
            ->expects($this->once())
            ->method('getClass')
            ->willReturn($innerClass);
        $grammar = $this->createMock(GrammarInterface::class);
        $queryBuilder = new SelectBuilder($grammar);
        $unions = [
            'union' => new SelectBuilder($grammar)
        ];

        $cache = $this->createMock(CacheInterface::class);
        $cacheKeySelector = function($key) { return 'prefix.' . $key; };
        $cacheTtl = 3600;

        $relation = Relations::cachedOneToOne(
            $relationKey,
            $table,
            $outerKey,
            $innerKey,
            $pdo,
            $fetcher,
            $queryBuilder,
            $unions,
            $cache,
            $cacheKeySelector,
            $cacheTtl
        )($outerClass);
        $relationStrategy = $relation->getRelationStrategy();
        $innerRelationStrategy = $relationStrategy->getRelationStrategy();
        $joinStrategy = $relation->getJoinStrategy();

        $this->assertInstanceOf(Relation::class, $relation);
        $this->assertSame($outerClass, $relation->getResultClass());

        $this->assertInstanceOf(Cached::class, $relationStrategy);
        $this->assertSame($cache, $relationStrategy->getCache());
        $this->assertSame($cacheKeySelector, $relationStrategy->getCacheKeySelector());
        $this->assertSame($cacheTtl, $relationStrategy->getCacheTtl());

        $this->assertInstanceOf(OneTo::class, $innerRelationStrategy);
        $this->assertSame($relationKey, $innerRelationStrategy->getRelationKey());
        $this->assertSame($table, $innerRelationStrategy->getTable());
        $this->assertSame($outerKey, $innerRelationStrategy->getOuterKey());
        $this->assertSame($innerKey, $innerRelationStrategy->getInnerKey());
        $this->assertSame($pdo, $innerRelationStrategy->getPdo());
        $this->assertSame($fetcher, $innerRelationStrategy->getFetcher());
        $this->assertSame($queryBuilder, $innerRelationStrategy->getQueryBuilder());
        $this->assertSame($unions, $innerRelationStrategy->getUnions());

        $this->assertInstanceOf(OuterJoin::class, $joinStrategy);
        $this->assertSame(123, ($joinStrategy->getOuterKeySelector())(new Model(['outer_key' => 123])));
        $this->assertSame(456, ($joinStrategy->getInnerKeySelector())(new Model(['inner_key' => 456])));
        $this->assertEquals(
            new Model(['outer_key' => 123, 'relation_key' => new Model(['inner_key' => 456])]),
            ($joinStrategy->getResultSelector())(
                new Model(['outer_key' => 123]),
                new Model(['inner_key' => 456])
            )
        );
    }

    public function testPreloadedOneToOne(): void
    {
        $outerClass = Model::class;
        $innerClass = Model::class;

        $relationKey = 'relation_key';
        $outerKey = 'outer_key';
        $innerKey = 'inner_key';
        $innerElements = [new Model([])];

        $relation = Relations::preloadedOneToOne(
            $relationKey,
            $outerKey,
            $innerKey,
            $innerClass,
            $innerElements
        )($outerClass);
        $relationStrategy = $relation->getRelationStrategy();
        $joinStrategy = $relation->getJoinStrategy();

        $this->assertInstanceOf(Relation::class, $relation);
        $this->assertSame($outerClass, $relation->getResultClass());

        $this->assertInstanceOf(Preloaded::class, $relationStrategy);
        $this->assertSame($relationKey, $relationStrategy->getRelationKey());
        $this->assertSame($outerKey, $relationStrategy->getOuterKey());
        $this->assertSame($innerKey, $relationStrategy->getInnerKey());
        $this->assertSame($innerElements, $relationStrategy->getInnerElements());

        $this->assertInstanceOf(OuterJoin::class, $joinStrategy);
        $this->assertSame(123, ($joinStrategy->getOuterKeySelector())(new Model(['outer_key' => 123])));
        $this->assertSame(456, ($joinStrategy->getInnerKeySelector())(new Model(['inner_key' => 456])));
        $this->assertEquals(
            new Model(['outer_key' => 123, 'relation_key' => new Model(['inner_key' => 456])]),
            ($joinStrategy->getResultSelector())(
                new Model(['outer_key' => 123]),
                new Model(['inner_key' => 456])
            )
        );
    }

    public function testPreloadedOneToMany(): void
    {
        $outerClass = Model::class;
        $innerClass = Model::class;

        $relationKey = 'relation_key';
        $outerKey = 'outer_key';
        $innerKey = 'inner_key';
        $innerElements = [new Model([])];

        $relation = Relations::preloadedOneToMany(
            $relationKey,
            $outerKey,
            $innerKey,
            $innerClass,
            $innerElements
        )($outerClass);
        $relationStrategy = $relation->getRelationStrategy();
        $joinStrategy = $relation->getJoinStrategy();

        $this->assertInstanceOf(Relation::class, $relation);
        $this->assertSame($outerClass, $relation->getResultClass());

        $this->assertInstanceOf(Preloaded::class, $relationStrategy);
        $this->assertSame($relationKey, $relationStrategy->getRelationKey());
        $this->assertSame($outerKey, $relationStrategy->getOuterKey());
        $this->assertSame($innerKey, $relationStrategy->getInnerKey());
        $this->assertSame($innerElements, $relationStrategy->getInnerElements());

        $this->assertInstanceOf(GroupJoin::class, $joinStrategy);
        $this->assertSame(123, ($joinStrategy->getOuterKeySelector())(new Model(['outer_key' => 123])));
        $this->assertSame(456, ($joinStrategy->getInnerKeySelector())(new Model(['inner_key' => 456])));
        $this->assertEquals(
            new Model(['outer_key' => 123, 'relation_key' => [new Model(['inner_key' => 456]), new Model(['inner_key' => 789])]]),
            ($joinStrategy->getResultSelector())(
                new Model(['outer_key' => 123]),
                [new Model(['inner_key' => 456]), new Model(['inner_key' => 789])]
            )
        );
    }

    public function testManyToMany(): void
    {
        $outerClass = Model::class;
        $innerClass = Model::class;

        $relationKey = 'relation_key';
        $oneToManyTable = 'one_to_many_table';
        $oneToManyOuterKey = 'one_to_many_outer_key';
        $oneToManyInnerKey = 'one_to_many_inner_key';
        $manyToOneTable = 'many_to_one_table';
        $manyToOneOuterKey = 'many_to_one_outer_key';
        $manyToOneInnerKey = 'many_to_one_inner_key';

        $pdo = $this->createMock(PDOInterface::class);
        $fetcher = $this->createMock(FetcherInterface::class);
        $fetcher
            ->expects($this->once())
            ->method('getClass')
            ->willReturn($innerClass);
        $grammar = $this->createMock(GrammarInterface::class);
        $queryBuilder = new SelectBuilder($grammar);

        $relation = Relations::manyToMany(
            $relationKey,
            $oneToManyTable,
            $oneToManyOuterKey,
            $oneToManyInnerKey,
            $manyToOneTable,
            $manyToOneOuterKey,
            $manyToOneInnerKey,
            $pdo,
            $fetcher,
            $queryBuilder
        )($outerClass);
        $relationStrategy = $relation->getRelationStrategy();
        $joinStrategy = $relation->getJoinStrategy();

        $this->assertInstanceOf(Relation::class, $relation);
        $this->assertSame($outerClass, $relation->getResultClass());

        $this->assertInstanceOf(ManyTo::class, $relationStrategy);
        $this->assertSame($relationKey, $relationStrategy->getRelationKey());
        $this->assertSame($oneToManyTable, $relationStrategy->getOneToManyTable());
        $this->assertSame($oneToManyOuterKey, $relationStrategy->getOneToManyOuterKey());
        $this->assertSame($oneToManyInnerKey, $relationStrategy->getOneToManyInnerKey());
        $this->assertSame($manyToOneTable, $relationStrategy->getManyToOneTable());
        $this->assertSame($manyToOneOuterKey, $relationStrategy->getManyToOneOuterKey());
        $this->assertSame($manyToOneInnerKey, $relationStrategy->getManyToOneInnerKey());
        $this->assertSame($pdo, $relationStrategy->getPdo());
        $this->assertSame($fetcher, $relationStrategy->getFetcher());
        $this->assertSame($queryBuilder, $relationStrategy->getQueryBuilder());

        $this->assertInstanceOf(GroupJoin::class, $joinStrategy);
        $this->assertSame(123, ($joinStrategy->getOuterKeySelector())(new Model(['one_to_many_outer_key' => 123])));
        $this->assertSame(456, ($joinStrategy->getInnerKeySelector())(new Model(['__pivot_one_to_many_inner_key' => 456])));
        $this->assertEquals(
            new Model(['outer_key' => 123, 'relation_key' => [new Model(['inner_key' => 456]), new Model(['inner_key' => 789])]]),
            ($joinStrategy->getResultSelector())(
                new Model(['outer_key' => 123]),
                [new Model(['inner_key' => 456]), new Model(['inner_key' => 789])]
            )
        );
    }

    public function testThroughManyToMany(): void
    {
        $outerClass = Model::class;
        $innerClass = Model::class;

        $relationKey = 'relation_key';
        $oneToManyTable = 'one_to_many_table';
        $oneToManyOuterKey = 'one_to_many_outer_key';
        $oneToManyInnerKey = 'one_to_many_inner_key';
        $manyToOneTable = 'many_to_one_table';
        $manyToOneOuterKey = 'many_to_one_outer_key';
        $manyToOneInnerKey = 'many_to_one_inner_key';
        $throughKey = 'through_key';

        $pdo = $this->createMock(PDOInterface::class);
        $fetcher = $this->createMock(FetcherInterface::class);
        $fetcher
            ->expects($this->once())
            ->method('getClass')
            ->willReturn($innerClass);
        $grammar = $this->createMock(GrammarInterface::class);
        $queryBuilder = new SelectBuilder($grammar);

        $relation = Relations::throughManyToMany(
            $relationKey,
            $oneToManyTable,
            $oneToManyOuterKey,
            $oneToManyInnerKey,
            $manyToOneTable,
            $manyToOneOuterKey,
            $manyToOneInnerKey,
            $throughKey,
            $pdo,
            $fetcher,
            $queryBuilder
        )($outerClass);
        $relationStrategy = $relation->getRelationStrategy();
        $joinStrategy = $relation->getJoinStrategy();

        $this->assertInstanceOf(Relation::class, $relation);
        $this->assertSame($outerClass, $relation->getResultClass());

        $this->assertInstanceOf(ManyTo::class, $relationStrategy);
        $this->assertSame($relationKey, $relationStrategy->getRelationKey());
        $this->assertSame($oneToManyTable, $relationStrategy->getOneToManyTable());
        $this->assertSame($oneToManyOuterKey, $relationStrategy->getOneToManyOuterKey());
        $this->assertSame($oneToManyInnerKey, $relationStrategy->getOneToManyInnerKey());
        $this->assertSame($manyToOneTable, $relationStrategy->getManyToOneTable());
        $this->assertSame($manyToOneOuterKey, $relationStrategy->getManyToOneOuterKey());
        $this->assertSame($manyToOneInnerKey, $relationStrategy->getManyToOneInnerKey());
        $this->assertSame($pdo, $relationStrategy->getPdo());
        $this->assertSame($fetcher, $relationStrategy->getFetcher());
        $this->assertSame($queryBuilder, $relationStrategy->getQueryBuilder());

        $this->assertInstanceOf(ThroughGroupJoin::class, $joinStrategy);
        $this->assertSame(123, ($joinStrategy->getOuterKeySelector())(new Model(['one_to_many_outer_key' => 123])));
        $this->assertSame(456, ($joinStrategy->getInnerKeySelector())(new Model(['__pivot_one_to_many_inner_key' => 456])));
        $this->assertSame(789, ($joinStrategy->getThroughKeySelector())(new Model(['through_key' => 789])));
        $this->assertEquals(
            new Model(['outer_key' => 123, 'relation_key' => [new Model(['inner_key' => 456]), new Model(['inner_key' => 789])]]),
            ($joinStrategy->getResultSelector())(
                new Model(['outer_key' => 123]),
                [new Model(['inner_key' => 456]), new Model(['inner_key' => 789])]
            )
        );
    }

    public function testPolymorphic(): void
    {
        $outerClass = Model::class;

        $morphKey = 'morph_key';
        $morphRelations = [
            'first' => $this->createMock(RelationInterface::class),
            'second' => $this->createMock(RelationInterface::class),
        ];

        $morphRelationFactories = [
            'first' => $this->createMock(Spy::class),
            'second' => $this->createMock(Spy::class),
        ];

        foreach ($morphRelationFactories as $key => $morphRelationFactory) {
            $morphRelationFactory
                ->expects($this->once())
                ->method('__invoke')
                ->with($this->identicalTo($outerClass))
                ->willReturn($morphRelations[$key]);
        }

        $relation = Relations::polymorphic(
            $morphKey,
            $morphRelationFactories
        )($outerClass);

        $this->assertInstanceOf(PolymorphicRelation::class, $relation);
        $this->assertSame($outerClass, $relation->getResultClass());
        $this->assertSame(123, ($relation->getMorphKeySelector())(new Model(['morph_key' => 123])));
        $this->assertSame($morphRelations, $relation->getRelations());
    }
}
