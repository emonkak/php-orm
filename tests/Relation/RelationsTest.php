<?php

namespace Emonkak\Orm\Tests\Relation;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\Grammar\GrammarInterface;
use Emonkak\Orm\Relation\Cached;
use Emonkak\Orm\Relation\CachedRelationStrategy;
use Emonkak\Orm\Relation\JoinStrategy\GroupJoin;
use Emonkak\Orm\Relation\JoinStrategy\LazyGroupJoin;
use Emonkak\Orm\Relation\JoinStrategy\LazyOuterJoin;
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
use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use Psr\SimpleCache\CacheInterface;

/**
 * @covers Emonkak\Orm\Relation\Relations
 */
class RelationsTest extends \PHPUnit_Framework_TestCase
{
    public function testOneToOne()
    {
        $relationKey = 'relation_key';
        $table = 'table';
        $outerKey = 'outer_key';
        $innerKey = 'inner_key';

        $pdo = $this->createMock(PDOInterface::class);
        $fetcher = $this->createMock(FetcherInterface::class);
        $grammar = $this->createMock(GrammarInterface::class);
        $builder = new SelectBuilder($grammar);

        $relation = Relations::oneToOne(
            $relationKey,
            $table,
            $outerKey,
            $innerKey,
            $pdo,
            $fetcher,
            $builder
        );

        $this->assertInstanceOf(Relation::class, $relation);
        $this->assertInstanceOf(OuterJoin::class, $relation->getJoinStrategy());

        $relationStrategy = $relation->getRelationStrategy();

        $this->assertInstanceOf(OneTo::class, $relationStrategy);
        $this->assertSame($relationKey, $relationStrategy->getRelationKey());
        $this->assertSame($table, $relationStrategy->getTable());
        $this->assertSame($outerKey, $relationStrategy->getOuterKey());
        $this->assertSame($innerKey, $relationStrategy->getInnerKey());
        $this->assertSame($pdo, $relationStrategy->getPdo());
        $this->assertSame($fetcher, $relationStrategy->getFetcher());
        $this->assertSame($builder, $relationStrategy->getBuilder());
    }

    public function testOneToMany()
    {
        $relationKey = 'relation_key';
        $table = 'table';
        $outerKey = 'outer_key';
        $innerKey = 'inner_key';

        $pdo = $this->createMock(PDOInterface::class);
        $fetcher = $this->createMock(FetcherInterface::class);
        $grammar = $this->createMock(GrammarInterface::class);
        $builder = new SelectBuilder($grammar);

        $relation = Relations::oneToMany(
            $relationKey,
            $table,
            $outerKey,
            $innerKey,
            $pdo,
            $fetcher,
            $builder
        );

        $this->assertInstanceOf(Relation::class, $relation);
        $this->assertInstanceOf(GroupJoin::class, $relation->getJoinStrategy());

        $relationStrategy = $relation->getRelationStrategy();

        $this->assertInstanceOf(OneTo::class, $relationStrategy);
        $this->assertSame($relationKey, $relationStrategy->getRelationKey());
        $this->assertSame($table, $relationStrategy->getTable());
        $this->assertSame($outerKey, $relationStrategy->getOuterKey());
        $this->assertSame($innerKey, $relationStrategy->getInnerKey());
        $this->assertSame($pdo, $relationStrategy->getPdo());
        $this->assertSame($fetcher, $relationStrategy->getFetcher());
        $this->assertSame($builder, $relationStrategy->getBuilder());
    }

    public function testThroughOneToOne()
    {
        $relationKey = 'relation_key';
        $table = 'table';
        $outerKey = 'outer_key';
        $innerKey = 'inner_key';
        $throughKey = 'through_key';

        $pdo = $this->createMock(PDOInterface::class);
        $fetcher = $this->createMock(FetcherInterface::class);
        $grammar = $this->createMock(GrammarInterface::class);
        $builder = new SelectBuilder($grammar);

        $relation = Relations::throughOneToOne(
            $relationKey,
            $table,
            $outerKey,
            $innerKey,
            $throughKey,
            $pdo,
            $fetcher,
            $builder
        );

        $this->assertInstanceOf(Relation::class, $relation);
        $this->assertInstanceOf(ThroughOuterJoin::class, $relation->getJoinStrategy());

        $relationStrategy = $relation->getRelationStrategy();

        $this->assertInstanceOf(OneTo::class, $relationStrategy);
        $this->assertSame($relationKey, $relationStrategy->getRelationKey());
        $this->assertSame($table, $relationStrategy->getTable());
        $this->assertSame($outerKey, $relationStrategy->getOuterKey());
        $this->assertSame($innerKey, $relationStrategy->getInnerKey());
        $this->assertSame($pdo, $relationStrategy->getPdo());
        $this->assertSame($fetcher, $relationStrategy->getFetcher());
        $this->assertSame($builder, $relationStrategy->getBuilder());
    }

    public function testThroughOneToMany()
    {
        $relationKey = 'relation_key';
        $table = 'table';
        $outerKey = 'outer_key';
        $innerKey = 'inner_key';
        $throughKey = 'through_key';

        $pdo = $this->createMock(PDOInterface::class);
        $fetcher = $this->createMock(FetcherInterface::class);
        $grammar = $this->createMock(GrammarInterface::class);
        $builder = new SelectBuilder($grammar);

        $relation = Relations::throughOneToMany(
            $relationKey,
            $table,
            $outerKey,
            $innerKey,
            $throughKey,
            $pdo,
            $fetcher,
            $builder
        );

        $this->assertInstanceOf(Relation::class, $relation);
        $this->assertInstanceOf(ThroughGroupJoin::class, $relation->getJoinStrategy());

        $relationStrategy = $relation->getRelationStrategy();

        $this->assertInstanceOf(OneTo::class, $relationStrategy);
        $this->assertSame($relationKey, $relationStrategy->getRelationKey());
        $this->assertSame($table, $relationStrategy->getTable());
        $this->assertSame($outerKey, $relationStrategy->getOuterKey());
        $this->assertSame($innerKey, $relationStrategy->getInnerKey());
        $this->assertSame($pdo, $relationStrategy->getPdo());
        $this->assertSame($fetcher, $relationStrategy->getFetcher());
        $this->assertSame($builder, $relationStrategy->getBuilder());
    }

    public function testLazyOneToOne()
    {
        $relationKey = 'relation_key';
        $table = 'table';
        $outerKey = 'outer_key';
        $innerKey = 'inner_key';

        $pdo = $this->createMock(PDOInterface::class);
        $fetcher = $this->createMock(FetcherInterface::class);
        $proxyFactory = new LazyLoadingValueHolderFactory();
        $grammar = $this->createMock(GrammarInterface::class);
        $builder = new SelectBuilder($grammar);

        $relation = Relations::lazyOneToOne(
            $relationKey,
            $table,
            $outerKey,
            $innerKey,
            $pdo,
            $fetcher,
            $builder,
            $proxyFactory
        );

        $this->assertInstanceOf(Relation::class, $relation);
        $this->assertInstanceOf(LazyOuterJoin::class, $relation->getJoinStrategy());

        $relationStrategy = $relation->getRelationStrategy();

        $this->assertInstanceOf(OneTo::class, $relationStrategy);
        $this->assertSame($relationKey, $relationStrategy->getRelationKey());
        $this->assertSame($table, $relationStrategy->getTable());
        $this->assertSame($outerKey, $relationStrategy->getOuterKey());
        $this->assertSame($innerKey, $relationStrategy->getInnerKey());
        $this->assertSame($pdo, $relationStrategy->getPdo());
        $this->assertSame($fetcher, $relationStrategy->getFetcher());
        $this->assertSame($builder, $relationStrategy->getBuilder());
    }

    public function testLazyOneToMany()
    {
        $relationKey = 'relation_key';
        $table = 'table';
        $outerKey = 'outer_key';
        $innerKey = 'inner_key';

        $pdo = $this->createMock(PDOInterface::class);
        $fetcher = $this->createMock(FetcherInterface::class);
        $proxyFactory = new LazyLoadingValueHolderFactory();
        $grammar = $this->createMock(GrammarInterface::class);
        $builder = new SelectBuilder($grammar);

        $relation = Relations::lazyOneToMany(
            $relationKey,
            $table,
            $outerKey,
            $innerKey,
            $pdo,
            $fetcher,
            $builder,
            $proxyFactory
        );

        $this->assertInstanceOf(Relation::class, $relation);
        $this->assertInstanceOf(LazyGroupJoin::class, $relation->getJoinStrategy());

        $relationStrategy = $relation->getRelationStrategy();

        $this->assertInstanceOf(OneTo::class, $relationStrategy);
        $this->assertSame($relationKey, $relationStrategy->getRelationKey());
        $this->assertSame($table, $relationStrategy->getTable());
        $this->assertSame($outerKey, $relationStrategy->getOuterKey());
        $this->assertSame($innerKey, $relationStrategy->getInnerKey());
        $this->assertSame($pdo, $relationStrategy->getPdo());
        $this->assertSame($fetcher, $relationStrategy->getFetcher());
        $this->assertSame($builder, $relationStrategy->getBuilder());
    }

    public function testCachedOneToOne()
    {
        $relationKey = 'relation_key';
        $outerKey = 'outer_key';
        $innerKey = 'inner_key';
        $table = 'table';

        $pdo = $this->createMock(PDOInterface::class);
        $fetcher = $this->createMock(FetcherInterface::class);
        $grammar = $this->createMock(GrammarInterface::class);
        $builder = new SelectBuilder($grammar);

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
            $builder,
            $cache,
            $cacheKeySelector,
            $cacheTtl
        );

        $this->assertInstanceOf(Relation::class, $relation);
        $this->assertInstanceOf(OuterJoin::class, $relation->getJoinStrategy());

        $relationStrategy = $relation->getRelationStrategy();

        $this->assertInstanceOf(Cached::class, $relationStrategy);
        $this->assertSame($cache, $relationStrategy->getCache());
        $this->assertSame($cacheKeySelector, $relationStrategy->getCacheKeySelector());
        $this->assertSame($cacheTtl, $relationStrategy->getCacheTtl());

        $innerRelationStrategy = $relationStrategy->getInnerRelationStrategy();

        $this->assertInstanceOf(OneTo::class, $innerRelationStrategy);
        $this->assertSame($relationKey, $innerRelationStrategy->getRelationKey());
        $this->assertSame($table, $innerRelationStrategy->getTable());
        $this->assertSame($outerKey, $innerRelationStrategy->getOuterKey());
        $this->assertSame($innerKey, $innerRelationStrategy->getInnerKey());
    }

    public function testPreloadedOneToOne()
    {
        $relationKey = 'relation_key';
        $outerKey = 'outer_key';
        $innerKey = 'inner_key';
        $innerClass = Model::class;
        $innerElements = [new Model([])];

        $relation = Relations::preloadedOneToOne(
            $relationKey,
            $outerKey,
            $innerKey,
            $innerClass,
            $innerElements
        );

        $this->assertInstanceOf(Relation::class, $relation);
        $this->assertInstanceOf(OuterJoin::class, $relation->getJoinStrategy());

        $relationStrategy = $relation->getRelationStrategy();

        $this->assertInstanceOf(Preloaded::class, $relationStrategy);
        $this->assertSame($relationKey, $relationStrategy->getRelationKey());
        $this->assertSame($outerKey, $relationStrategy->getOuterKey());
        $this->assertSame($innerKey, $relationStrategy->getInnerKey());
        $this->assertSame($innerClass, $relationStrategy->getInnerClass());
        $this->assertSame($innerElements, $relationStrategy->getInnerElements());
    }

    public function testPreloadedOneToMany()
    {
        $relationKey = 'relation_key';
        $outerKey = 'outer_key';
        $innerKey = 'inner_key';
        $innerClass = Model::class;
        $innerElements = [new Model([])];

        $relation = Relations::preloadedOneToMany(
            $relationKey,
            $outerKey,
            $innerKey,
            $innerClass,
            $innerElements
        );

        $this->assertInstanceOf(Relation::class, $relation);
        $this->assertInstanceOf(GroupJoin::class, $relation->getJoinStrategy());

        $relationStrategy = $relation->getRelationStrategy();

        $this->assertInstanceOf(Preloaded::class, $relationStrategy);
        $this->assertSame($relationKey, $relationStrategy->getRelationKey());
        $this->assertSame($outerKey, $relationStrategy->getOuterKey());
        $this->assertSame($innerKey, $relationStrategy->getInnerKey());
        $this->assertSame($innerClass, $relationStrategy->getInnerClass());
        $this->assertSame($innerElements, $relationStrategy->getInnerElements());
    }

    public function testManyToMany()
    {
        $relationKey = 'relation_key';
        $oneToManyTable = 'one_to_many_table';
        $oneToManyOuterKey = 'one_to_many_outer_key';
        $oneToManyInnerKey = 'one_to_many_inner_key';
        $manyToOneTable = 'many_to_one_table';
        $manyToOneOuterKey = 'many_to_one_outer_key';
        $manyToOneInnerKey = 'many_to_one_inner_key';

        $pdo = $this->createMock(PDOInterface::class);
        $fetcher = $this->createMock(FetcherInterface::class);
        $grammar = $this->createMock(GrammarInterface::class);
        $builder = new SelectBuilder($grammar);

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
            $builder
        );

        $this->assertInstanceOf(Relation::class, $relation);
        $this->assertInstanceOf(GroupJoin::class, $relation->getJoinStrategy());

        $relationStrategy = $relation->getRelationStrategy();

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
        $this->assertSame($builder, $relationStrategy->getBuilder());
    }

    public function testThroughManyToMany()
    {
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
        $grammar = $this->createMock(GrammarInterface::class);
        $builder = new SelectBuilder($grammar);

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
            $builder
        );

        $this->assertInstanceOf(Relation::class, $relation);
        $this->assertInstanceOf(ThroughGroupJoin::class, $relation->getJoinStrategy());

        $relationStrategy = $relation->getRelationStrategy();

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
        $this->assertSame($builder, $relationStrategy->getBuilder());
    }

    public function testPolymorphic()
    {
        $polymorphics = [
            'morph_key1' => $this->createMock(RelationInterface::class),
            'morph_key2' => $this->createMock(RelationInterface::class),
        ];

        $relation = Relations::polymorphic(
            'morph_key',
            $polymorphics
        );

        $this->assertInstanceOf(PolymorphicRelation::class, $relation);
        $this->assertSame('morph_key', $relation->getMorphKey());
        $this->assertSame($polymorphics, $relation->getPolymorphics());
    }
}
