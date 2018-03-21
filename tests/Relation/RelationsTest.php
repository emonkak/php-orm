<?php

namespace Emonkak\Orm\Tests\Relation;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\Grammar\GrammarInterface;
use Emonkak\Orm\Relation\CachedRelation;
use Emonkak\Orm\Relation\JoinStrategy\GroupJoin;
use Emonkak\Orm\Relation\JoinStrategy\LazyGroupJoin;
use Emonkak\Orm\Relation\JoinStrategy\LazyOuterJoin;
use Emonkak\Orm\Relation\JoinStrategy\OuterJoin;
use Emonkak\Orm\Relation\JoinStrategy\ThroughGroupJoin;
use Emonkak\Orm\Relation\ManyToMany;
use Emonkak\Orm\Relation\Polymorphic;
use Emonkak\Orm\Relation\Relation;
use Emonkak\Orm\Relation\RelationInterface;
use Emonkak\Orm\Relation\Relations;
use Emonkak\Orm\SelectBuilder;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use Psr\SimpleCache\CacheInterface;

/**
 * @covers Emonkak\Orm\Relation\Relations
 */
class RelationsTest extends \PHPUnit_Framework_TestCase
{
    public function testOneToOne()
    {
        $pdo = $this->createMock(PDOInterface::class);
        $fetcher = $this->createMock(FetcherInterface::class);
        $grammar = $this->createMock(GrammarInterface::class);
        $builder = new SelectBuilder($grammar);

        $relation = Relations::oneToOne(
            'relation_key',
            'table',
            'outer_key',
            'inner_key',
            $pdo,
            $fetcher,
            $builder
        );

        $this->assertInstanceOf(Relation::class, $relation);
        $this->assertSame('relation_key', $relation->getRelationKey());
        $this->assertSame('table', $relation->getTable());
        $this->assertSame('outer_key', $relation->getOuterKey());
        $this->assertSame('inner_key', $relation->getInnerKey());
        $this->assertSame($pdo, $relation->getPdo());
        $this->assertSame($fetcher, $relation->getFetcher());
        $this->assertSame($builder, $relation->getBuilder());
        $this->assertInstanceOf(OuterJoin::class, $relation->getJoinStrategy());
    }

    public function testOneToMany()
    {
        $pdo = $this->createMock(PDOInterface::class);
        $fetcher = $this->createMock(FetcherInterface::class);
        $grammar = $this->createMock(GrammarInterface::class);
        $builder = new SelectBuilder($grammar);

        $relation = Relations::oneToMany(
            'relation_key',
            'table',
            'outer_key',
            'inner_key',
            $pdo,
            $fetcher,
            $builder
        );

        $this->assertInstanceOf(Relation::class, $relation);
        $this->assertSame('relation_key', $relation->getRelationKey());
        $this->assertSame('table', $relation->getTable());
        $this->assertSame('outer_key', $relation->getOuterKey());
        $this->assertSame('inner_key', $relation->getInnerKey());
        $this->assertSame($pdo, $relation->getPdo());
        $this->assertSame($fetcher, $relation->getFetcher());
        $this->assertSame($builder, $relation->getBuilder());
        $this->assertInstanceOf(GroupJoin::class, $relation->getJoinStrategy());
    }

    public function testThroughOneToMany()
    {
        $pdo = $this->createMock(PDOInterface::class);
        $fetcher = $this->createMock(FetcherInterface::class);
        $grammar = $this->createMock(GrammarInterface::class);
        $builder = new SelectBuilder($grammar);

        $relation = Relations::throughOneToMany(
            'relation_key',
            'table',
            'outer_key',
            'inner_key',
            'through_key',
            $pdo,
            $fetcher,
            $builder
        );

        $this->assertInstanceOf(Relation::class, $relation);
        $this->assertSame('relation_key', $relation->getRelationKey());
        $this->assertSame('table', $relation->getTable());
        $this->assertSame('outer_key', $relation->getOuterKey());
        $this->assertSame('inner_key', $relation->getInnerKey());
        $this->assertSame($pdo, $relation->getPdo());
        $this->assertSame($fetcher, $relation->getFetcher());
        $this->assertSame($builder, $relation->getBuilder());
        $this->assertInstanceOf(ThroughGroupJoin::class, $relation->getJoinStrategy());
    }

    public function testLazyOneToOne()
    {
        $pdo = $this->createMock(PDOInterface::class);
        $fetcher = $this->createMock(FetcherInterface::class);
        $proxyFactory = new LazyLoadingValueHolderFactory();
        $grammar = $this->createMock(GrammarInterface::class);
        $builder = new SelectBuilder($grammar);

        $relation = Relations::lazyOneToOne(
            'relation_key',
            'table',
            'outer_key',
            'inner_key',
            $pdo,
            $fetcher,
            $builder,
            $proxyFactory
        );

        $this->assertInstanceOf(Relation::class, $relation);
        $this->assertSame('relation_key', $relation->getRelationKey());
        $this->assertSame('table', $relation->getTable());
        $this->assertSame('outer_key', $relation->getOuterKey());
        $this->assertSame('inner_key', $relation->getInnerKey());
        $this->assertSame($pdo, $relation->getPdo());
        $this->assertSame($fetcher, $relation->getFetcher());
        $this->assertSame($builder, $relation->getBuilder());
        $this->assertInstanceOf(LazyOuterJoin::class, $relation->getJoinStrategy());
    }

    public function testLazyOneToMany()
    {
        $pdo = $this->createMock(PDOInterface::class);
        $fetcher = $this->createMock(FetcherInterface::class);
        $proxyFactory = new LazyLoadingValueHolderFactory();
        $grammar = $this->createMock(GrammarInterface::class);
        $builder = new SelectBuilder($grammar);

        $relation = Relations::lazyOneToMany(
            'relation_key',
            'table',
            'outer_key',
            'inner_key',
            $pdo,
            $fetcher,
            $builder,
            $proxyFactory
        );

        $this->assertInstanceOf(Relation::class, $relation);
        $this->assertSame('relation_key', $relation->getRelationKey());
        $this->assertSame('table', $relation->getTable());
        $this->assertSame('outer_key', $relation->getOuterKey());
        $this->assertSame('inner_key', $relation->getInnerKey());
        $this->assertSame($pdo, $relation->getPdo());
        $this->assertSame($fetcher, $relation->getFetcher());
        $this->assertSame($builder, $relation->getBuilder());
        $this->assertInstanceOf(LazyGroupJoin::class, $relation->getJoinStrategy());
    }

    public function testCachedOneToOne()
    {
        $pdo = $this->createMock(PDOInterface::class);
        $fetcher = $this->createMock(FetcherInterface::class);
        $cache = $this->createMock(CacheInterface::class);
        $grammar = $this->createMock(GrammarInterface::class);
        $builder = new SelectBuilder($grammar);

        $relation = Relations::cachedOneToOne(
            'relation_key',
            'table',
            'outer_key',
            'inner_key',
            $pdo,
            $fetcher,
            $builder,
            $cache,
            'cache_prefix',
            3600
        );

        $this->assertInstanceOf(CachedRelation::class, $relation);
        $this->assertSame('relation_key', $relation->getRelationKey());
        $this->assertSame('table', $relation->getTable());
        $this->assertSame('outer_key', $relation->getOuterKey());
        $this->assertSame('inner_key', $relation->getInnerKey());
        $this->assertSame($pdo, $relation->getPdo());
        $this->assertSame($fetcher, $relation->getFetcher());
        $this->assertSame($cache, $relation->getCache());
        $this->assertSame('cache_prefix', $relation->getCachePrefix());
        $this->assertSame(3600, $relation->getCacheTtl());
        $this->assertSame($builder, $relation->getBuilder());
        $this->assertInstanceOf(OuterJoin::class, $relation->getJoinStrategy());
    }

    public function testCachedOneToMany()
    {
        $pdo = $this->createMock(PDOInterface::class);
        $fetcher = $this->createMock(FetcherInterface::class);
        $cache = $this->createMock(CacheInterface::class);
        $grammar = $this->createMock(GrammarInterface::class);
        $builder = new SelectBuilder($grammar);

        $relation = Relations::cachedOneToMany(
            'relation_key',
            'table',
            'outer_key',
            'inner_key',
            $pdo,
            $fetcher,
            $builder,
            $cache,
            'cache_prefix',
            3600
        );

        $this->assertInstanceOf(CachedRelation::class, $relation);
        $this->assertSame('relation_key', $relation->getRelationKey());
        $this->assertSame('table', $relation->getTable());
        $this->assertSame('outer_key', $relation->getOuterKey());
        $this->assertSame('inner_key', $relation->getInnerKey());
        $this->assertSame($pdo, $relation->getPdo());
        $this->assertSame($fetcher, $relation->getFetcher());
        $this->assertSame($cache, $relation->getCache());
        $this->assertSame('cache_prefix', $relation->getCachePrefix());
        $this->assertSame(3600, $relation->getCacheTtl());
        $this->assertSame($builder, $relation->getBuilder());
        $this->assertInstanceOf(GroupJoin::class, $relation->getJoinStrategy());
    }

    public function testManyToMany()
    {
        $pdo = $this->createMock(PDOInterface::class);
        $fetcher = $this->createMock(FetcherInterface::class);
        $grammar = $this->createMock(GrammarInterface::class);
        $builder = new SelectBuilder($grammar);

        $relation = Relations::manyToMany(
            'relation_key',
            'one_to_many_table',
            'one_to_many_outer_key',
            'one_to_many_inner_key',
            'many_to_one_table',
            'many_to_one_outer_key',
            'many_to_one_inner_key',
            $pdo,
            $fetcher,
            $builder
        );

        $this->assertInstanceOf(ManyToMany::class, $relation);
        $this->assertSame('relation_key', $relation->getRelationKey());
        $this->assertSame('one_to_many_table', $relation->getOneToManyTable());
        $this->assertSame('one_to_many_outer_key', $relation->getOneToManyOuterKey());
        $this->assertSame('one_to_many_inner_key', $relation->getOneToManyInnerKey());
        $this->assertSame('many_to_one_table', $relation->getManyToOneTable());
        $this->assertSame('many_to_one_outer_key', $relation->getManyToOneOuterKey());
        $this->assertSame('many_to_one_inner_key', $relation->getManyToOneInnerKey());
        $this->assertSame($pdo, $relation->getPdo());
        $this->assertSame($fetcher, $relation->getFetcher());
        $this->assertSame($builder, $relation->getBuilder());
        $this->assertInstanceOf(GroupJoin::class, $relation->getJoinStrategy());
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

        $this->assertInstanceOf(Polymorphic::class, $relation);
        $this->assertSame('morph_key', $relation->getMorphKey());
        $this->assertSame($polymorphics, $relation->getPolymorphics());
    }
}
