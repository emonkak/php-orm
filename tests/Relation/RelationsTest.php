<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\Relation;

use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\Grammar\GrammarInterface;
use Emonkak\Orm\Relation\Cached;
use Emonkak\Orm\Relation\JoinStrategy\GroupJoin;
use Emonkak\Orm\Relation\JoinStrategy\LazyCollection;
use Emonkak\Orm\Relation\JoinStrategy\LazyGroupJoin;
use Emonkak\Orm\Relation\JoinStrategy\LazyOuterJoin;
use Emonkak\Orm\Relation\JoinStrategy\LazyValue;
use Emonkak\Orm\Relation\JoinStrategy\OuterJoin;
use Emonkak\Orm\Relation\ManyTo;
use Emonkak\Orm\Relation\OneTo;
use Emonkak\Orm\Relation\PolymorphicRelation;
use Emonkak\Orm\Relation\Preloaded;
use Emonkak\Orm\Relation\Relation;
use Emonkak\Orm\Relation\RelationInterface;
use Emonkak\Orm\Relation\Relations;
use Emonkak\Orm\SelectBuilder;
use Emonkak\Orm\Tests\Fixtures\Model;
use Emonkak\Orm\Tests\Fixtures\Spy;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;

/**
 * @covers \Emonkak\Orm\Relation\Relations
 */
class RelationsTest extends TestCase
{
    public function testOneToOne(): void
    {
        $outerClass = Model::class;
        $innerClass = Model::class;

        $relationKeyName = 'relation_key';
        $tableName = 'table';
        $outerKeyName = 'outer_key';
        $innerKeyName = 'inner_key';

        $grammar = $this->createMock(GrammarInterface::class);
        $queryBuilder = new SelectBuilder($grammar);
        $fetcher = $this->createMock(FetcherInterface::class);
        $fetcher
            ->expects($this->once())
            ->method('getClass')
            ->willReturn($innerClass);

        /** @var Relation<Model,Model,int,Model> */
        $relation = Relations::oneToOne(
            $relationKeyName,
            $tableName,
            $outerKeyName,
            $innerKeyName,
            $queryBuilder,
            $fetcher
        )($outerClass);
        $relationStrategy = $relation->getRelationStrategy();
        $joinStrategy = $relation->getJoinStrategy();

        $this->assertInstanceOf(Relation::class, $relation);
        $this->assertSame($outerClass, $relation->getResultClass());

        $this->assertInstanceOf(OneTo::class, $relationStrategy);
        $this->assertSame($relationKeyName, $relationStrategy->getRelationKeyName());
        $this->assertSame($tableName, $relationStrategy->getTableName());
        $this->assertSame($outerKeyName, $relationStrategy->getOuterKeyName());
        $this->assertSame($innerKeyName, $relationStrategy->getInnerKeyName());
        $this->assertSame($queryBuilder, $relationStrategy->getQueryBuilder());
        $this->assertSame($fetcher, $relationStrategy->getFetcher());

        $this->assertInstanceOf(OuterJoin::class, $joinStrategy);
        $this->assertSame(123, ($joinStrategy->getOuterKeySelector())(new Model(['outer_key' => 123])));
        $this->assertSame(456, ($joinStrategy->getInnerKeySelector())(new Model(['inner_key' => 456])));
        $this->assertEquals(
            new Model([
                'outer_key' => 123,
                'relation_key' => new Model(['inner_key' => 456]),
            ]),
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

        $relationKeyName = 'relation_key';
        $tableName = 'table';
        $outerKeyName = 'outer_key';
        $innerKeyName = 'inner_key';

        $grammar = $this->createMock(GrammarInterface::class);
        $queryBuilder = new SelectBuilder($grammar);
        $fetcher = $this->createMock(FetcherInterface::class);
        $fetcher
            ->expects($this->once())
            ->method('getClass')
            ->willReturn($innerClass);

        $collationClass = \ArrayObject::class;

        /** @var Relation<Model,Model,int,Model> */
        $relation = Relations::oneToMany(
            $relationKeyName,
            $tableName,
            $outerKeyName,
            $innerKeyName,
            $queryBuilder,
            $fetcher,
            $collationClass
        )($outerClass);
        $relationStrategy = $relation->getRelationStrategy();
        $joinStrategy = $relation->getJoinStrategy();

        $this->assertInstanceOf(Relation::class, $relation);
        $this->assertSame($outerClass, $relation->getResultClass());

        $this->assertInstanceOf(OneTo::class, $relationStrategy);
        $this->assertSame($relationKeyName, $relationStrategy->getRelationKeyName());
        $this->assertSame($tableName, $relationStrategy->getTableName());
        $this->assertSame($outerKeyName, $relationStrategy->getOuterKeyName());
        $this->assertSame($innerKeyName, $relationStrategy->getInnerKeyName());
        $this->assertSame($queryBuilder, $relationStrategy->getQueryBuilder());
        $this->assertSame($fetcher, $relationStrategy->getFetcher());

        $this->assertInstanceOf(GroupJoin::class, $joinStrategy);
        $this->assertSame(123, ($joinStrategy->getOuterKeySelector())(new Model(['outer_key' => 123])));
        $this->assertSame(456, ($joinStrategy->getInnerKeySelector())(new Model(['inner_key' => 456])));
        $this->assertEquals(
            new Model([
                'outer_key' => 123,
                'relation_key' => new \ArrayObject([
                    new Model(['inner_key' => 456]),
                    new Model(['inner_key' => 789]),
                ]),
            ]),
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

        $relationKeyName = 'relation_key';
        $tableName = 'table';
        $outerKeyName = 'outer_key';
        $innerKeyName = 'inner_key';
        $throughKey = 'through_key';

        $grammar = $this->createMock(GrammarInterface::class);
        $queryBuilder = new SelectBuilder($grammar);
        $fetcher = $this->createMock(FetcherInterface::class);
        $fetcher
            ->expects($this->once())
            ->method('getClass')
            ->willReturn($innerClass);

        /** @var Relation<Model,Model,int,Model> */
        $relation = Relations::throughOneToOne(
            $relationKeyName,
            $tableName,
            $outerKeyName,
            $innerKeyName,
            $throughKey,
            $queryBuilder,
            $fetcher
        )($outerClass);
        $relationStrategy = $relation->getRelationStrategy();
        $joinStrategy = $relation->getJoinStrategy();

        $this->assertInstanceOf(Relation::class, $relation);
        $this->assertSame($outerClass, $relation->getResultClass());

        $this->assertInstanceOf(OneTo::class, $relationStrategy);
        $this->assertSame($relationKeyName, $relationStrategy->getRelationKeyName());
        $this->assertSame($tableName, $relationStrategy->getTableName());
        $this->assertSame($outerKeyName, $relationStrategy->getOuterKeyName());
        $this->assertSame($innerKeyName, $relationStrategy->getInnerKeyName());
        $this->assertSame($queryBuilder, $relationStrategy->getQueryBuilder());
        $this->assertSame($fetcher, $relationStrategy->getFetcher());

        $this->assertInstanceOf(OuterJoin::class, $joinStrategy);
        $this->assertSame(123, ($joinStrategy->getOuterKeySelector())(new Model(['outer_key' => 123])));
        $this->assertSame(456, ($joinStrategy->getInnerKeySelector())(new Model(['inner_key' => 456])));
        $this->assertEquals(
            new Model([
                'outer_key' => 123,
                'relation_key' => 'foo',
            ]),
            ($joinStrategy->getResultSelector())(
                new Model(['outer_key' => 123]),
                new Model(['inner_key' => 456, 'through_key' => 'foo'])
            )
        );
    }

    public function testThroughOneToMany(): void
    {
        $outerClass = Model::class;
        $innerClass = Model::class;

        $relationKeyName = 'relation_key';
        $tableName = 'table';
        $outerKeyName = 'outer_key';
        $innerKeyName = 'inner_key';
        $throughKey = 'through_key';

        $grammar = $this->createMock(GrammarInterface::class);
        $queryBuilder = new SelectBuilder($grammar);
        $fetcher = $this->createMock(FetcherInterface::class);
        $fetcher
            ->expects($this->once())
            ->method('getClass')
            ->willReturn($innerClass);

        /** @var Relation<Model,Model,int,Model> */
        $relation = Relations::throughOneToMany(
            $relationKeyName,
            $tableName,
            $outerKeyName,
            $innerKeyName,
            $throughKey,
            $queryBuilder,
            $fetcher
        )($outerClass);
        $relationStrategy = $relation->getRelationStrategy();
        $joinStrategy = $relation->getJoinStrategy();

        $this->assertInstanceOf(Relation::class, $relation);
        $this->assertSame($outerClass, $relation->getResultClass());

        $this->assertInstanceOf(OneTo::class, $relationStrategy);
        $this->assertSame($relationKeyName, $relationStrategy->getRelationKeyName());
        $this->assertSame($tableName, $relationStrategy->getTableName());
        $this->assertSame($outerKeyName, $relationStrategy->getOuterKeyName());
        $this->assertSame($innerKeyName, $relationStrategy->getInnerKeyName());
        $this->assertSame($queryBuilder, $relationStrategy->getQueryBuilder());
        $this->assertSame($fetcher, $relationStrategy->getFetcher());

        $this->assertInstanceOf(GroupJoin::class, $joinStrategy);
        $this->assertSame(123, ($joinStrategy->getOuterKeySelector())(new Model(['outer_key' => 123])));
        $this->assertSame(456, ($joinStrategy->getInnerKeySelector())(new Model(['inner_key' => 456])));
        $this->assertEquals(
            new Model([
                'outer_key' => 123,
                'relation_key' => [
                    'foo',
                    'bar',
                ],
            ]),
            ($joinStrategy->getResultSelector())(
                new Model(['outer_key' => 123]),
                [new Model(['inner_key' => 456, 'through_key' => 'foo']), new Model(['inner_key' => 789, 'through_key' => 'bar'])]
            )
        );
    }

    public function testLazyOneToOne(): void
    {
        $outerClass = Model::class;
        $innerClass = Model::class;

        $relationKeyName = 'relation_key';
        $tableName = 'table';
        $outerKeyName = 'outer_key';
        $innerKeyName = 'inner_key';

        $grammar = $this->createMock(GrammarInterface::class);
        $queryBuilder = new SelectBuilder($grammar);
        $fetcher = $this->createMock(FetcherInterface::class);
        $fetcher
            ->expects($this->once())
            ->method('getClass')
            ->willReturn($innerClass);

        /** @var Relation<Model,Model,int,Model> */
        $relation = Relations::lazyOneToOne(
            $relationKeyName,
            $tableName,
            $outerKeyName,
            $innerKeyName,
            $queryBuilder,
            $fetcher
        )($outerClass);
        $relationStrategy = $relation->getRelationStrategy();
        $joinStrategy = $relation->getJoinStrategy();

        $this->assertInstanceOf(Relation::class, $relation);
        $this->assertSame($outerClass, $relation->getResultClass());

        $this->assertInstanceOf(OneTo::class, $relationStrategy);
        $this->assertSame($relationKeyName, $relationStrategy->getRelationKeyName());
        $this->assertSame($tableName, $relationStrategy->getTableName());
        $this->assertSame($outerKeyName, $relationStrategy->getOuterKeyName());
        $this->assertSame($innerKeyName, $relationStrategy->getInnerKeyName());
        $this->assertSame($queryBuilder, $relationStrategy->getQueryBuilder());
        $this->assertSame($fetcher, $relationStrategy->getFetcher());

        $this->assertInstanceOf(LazyOuterJoin::class, $joinStrategy);
        $this->assertSame(123, ($joinStrategy->getOuterKeySelector())(new Model(['outer_key' => 123])));
        $this->assertSame(456, ($joinStrategy->getInnerKeySelector())(new Model(['inner_key' => 456])));

        $outer = new Model(['outer_key' => 123]);
        /** @var LazyValue<Model|null,int> */
        $inner = new LazyValue(456, function(int $key): Model { return new Model(['inner_key' => $key]); });
        $result = ($joinStrategy->getResultSelector())($outer, $inner);

        $this->assertSame(123, $result->outer_key);
        $this->assertEquals(new Model(['inner_key' => 456]), $result->relation_key->get());
    }

    public function testLazyOneToMany(): void
    {
        $outerClass = Model::class;
        $innerClass = Model::class;

        $relationKeyName = 'relation_key';
        $tableName = 'table';
        $outerKeyName = 'outer_key';
        $innerKeyName = 'inner_key';

        $grammar = $this->createMock(GrammarInterface::class);
        $queryBuilder = new SelectBuilder($grammar);
        $fetcher = $this->createMock(FetcherInterface::class);
        $fetcher
            ->expects($this->once())
            ->method('getClass')
            ->willReturn($innerClass);

        /** @var Relation<Model,Model,int,Model> */
        $relation = Relations::lazyOneToMany(
            $relationKeyName,
            $tableName,
            $outerKeyName,
            $innerKeyName,
            $queryBuilder,
            $fetcher
        )($outerClass);
        $relationStrategy = $relation->getRelationStrategy();
        $joinStrategy = $relation->getJoinStrategy();

        $this->assertInstanceOf(Relation::class, $relation);
        $this->assertSame($outerClass, $relation->getResultClass());

        $this->assertInstanceOf(OneTo::class, $relationStrategy);
        $this->assertSame($relationKeyName, $relationStrategy->getRelationKeyName());
        $this->assertSame($tableName, $relationStrategy->getTableName());
        $this->assertSame($outerKeyName, $relationStrategy->getOuterKeyName());
        $this->assertSame($innerKeyName, $relationStrategy->getInnerKeyName());
        $this->assertSame($queryBuilder, $relationStrategy->getQueryBuilder());
        $this->assertSame($fetcher, $relationStrategy->getFetcher());

        $this->assertInstanceOf(LazyGroupJoin::class, $joinStrategy);
        $this->assertSame(123, ($joinStrategy->getOuterKeySelector())(new Model(['outer_key' => 123])));
        $this->assertSame(456, ($joinStrategy->getInnerKeySelector())(new Model(['inner_key' => 456])));

        $outer = new Model(['outer_key' => 123]);
        /** @var LazyCollection<Model,int> */
        $inner = new LazyCollection(123, function(int $key): array {
            return [
                new Model(['outer_key' => $key, 'inner_key' => 456]),
                new Model(['outer_key' => $key, 'inner_key' => 789]),
            ];
        });
        $result = ($joinStrategy->getResultSelector())($outer, $inner);

        $this->assertSame(123, $result->outer_key);
        $this->assertEquals([
            new Model(['outer_key' => 123, 'inner_key' => 456]),
            new Model(['outer_key' => 123, 'inner_key' => 789]),
        ], $result->relation_key->get());
    }

    public function testCachedOneToOne(): void
    {
        $outerClass = Model::class;
        $innerClass = Model::class;

        $relationKeyName = 'relation_key';
        $outerKeyName = 'outer_key';
        $innerKeyName = 'inner_key';
        $tableName = 'table';

        $grammar = $this->createMock(GrammarInterface::class);
        $queryBuilder = new SelectBuilder($grammar);
        $fetcher = $this->createMock(FetcherInterface::class);
        $fetcher
            ->expects($this->once())
            ->method('getClass')
            ->willReturn($innerClass);

        $cache = $this->createMock(CacheInterface::class);
        $cacheKeySelector = function(int $key): string { return 'prefix.' . $key; };
        $cacheTtl = 3600;

        /** @var Relation<Model,Model,int,Model> */
        $relation = Relations::cachedOneToOne(
            $relationKeyName,
            $tableName,
            $outerKeyName,
            $innerKeyName,
            $queryBuilder,
            $fetcher,
            $cache,
            $cacheKeySelector,
            $cacheTtl
        )($outerClass);
        $relationStrategy = $relation->getRelationStrategy();
        $joinStrategy = $relation->getJoinStrategy();

        $this->assertInstanceOf(Relation::class, $relation);
        $this->assertSame($outerClass, $relation->getResultClass());

        $this->assertInstanceOf(Cached::class, $relationStrategy);
        $this->assertSame($cache, $relationStrategy->getCache());
        $this->assertSame($cacheKeySelector, $relationStrategy->getCacheKeySelector());
        $this->assertSame($cacheTtl, $relationStrategy->getCacheTtl());

        $innerRelationStrategy = $relationStrategy->getRelationStrategy();
        $this->assertInstanceOf(OneTo::class, $innerRelationStrategy);
        $this->assertSame($relationKeyName, $innerRelationStrategy->getRelationKeyName());
        $this->assertSame($tableName, $innerRelationStrategy->getTableName());
        $this->assertSame($outerKeyName, $innerRelationStrategy->getOuterKeyName());
        $this->assertSame($innerKeyName, $innerRelationStrategy->getInnerKeyName());
        $this->assertSame($queryBuilder, $innerRelationStrategy->getQueryBuilder());
        $this->assertSame($fetcher, $innerRelationStrategy->getFetcher());

        $this->assertInstanceOf(OuterJoin::class, $joinStrategy);
        $this->assertSame(123, ($joinStrategy->getOuterKeySelector())(new Model(['outer_key' => 123])));
        $this->assertSame(456, ($joinStrategy->getInnerKeySelector())(new Model(['inner_key' => 456])));
        $this->assertEquals(
            new Model([
                'outer_key' => 123,
                'relation_key' => new Model(['inner_key' => 456]),
            ]),
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

        $relationKeyName = 'relation_key';
        $outerKeyName = 'outer_key';
        $innerKeyName = 'inner_key';
        $innerElements = [new Model([])];

        /** @var Relation<Model,Model,int,Model> */
        $relation = Relations::preloadedOneToOne(
            $relationKeyName,
            $outerKeyName,
            $innerKeyName,
            $innerClass,
            $innerElements
        )($outerClass);
        $relationStrategy = $relation->getRelationStrategy();
        $joinStrategy = $relation->getJoinStrategy();

        $this->assertInstanceOf(Relation::class, $relation);
        $this->assertSame($outerClass, $relation->getResultClass());

        $this->assertInstanceOf(Preloaded::class, $relationStrategy);
        $this->assertSame($relationKeyName, $relationStrategy->getRelationKeyName());
        $this->assertSame($outerKeyName, $relationStrategy->getOuterKeyName());
        $this->assertSame($innerKeyName, $relationStrategy->getInnerKeyName());
        $this->assertSame($innerElements, $relationStrategy->getInnerElements());

        $this->assertInstanceOf(OuterJoin::class, $joinStrategy);
        $this->assertSame(123, ($joinStrategy->getOuterKeySelector())(new Model(['outer_key' => 123])));
        $this->assertSame(456, ($joinStrategy->getInnerKeySelector())(new Model(['inner_key' => 456])));
        $this->assertEquals(
            new Model([
                'outer_key' => 123,
                'relation_key' => new Model(['inner_key' => 456]),
            ]),
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

        $relationKeyName = 'relation_key';
        $outerKeyName = 'outer_key';
        $innerKeyName = 'inner_key';
        $innerElements = [new Model([])];
        $collationClass = \ArrayObject::class;

        /** @var Relation<Model,Model,int,Model> */
        $relation = Relations::preloadedOneToMany(
            $relationKeyName,
            $outerKeyName,
            $innerKeyName,
            $innerClass,
            $innerElements,
            $collationClass
        )($outerClass);
        $relationStrategy = $relation->getRelationStrategy();
        $joinStrategy = $relation->getJoinStrategy();

        $this->assertInstanceOf(Relation::class, $relation);
        $this->assertSame($outerClass, $relation->getResultClass());

        $this->assertInstanceOf(Preloaded::class, $relationStrategy);
        $this->assertSame($relationKeyName, $relationStrategy->getRelationKeyName());
        $this->assertSame($outerKeyName, $relationStrategy->getOuterKeyName());
        $this->assertSame($innerKeyName, $relationStrategy->getInnerKeyName());
        $this->assertSame($innerElements, $relationStrategy->getInnerElements());

        $this->assertInstanceOf(GroupJoin::class, $joinStrategy);
        $this->assertSame(123, ($joinStrategy->getOuterKeySelector())(new Model(['outer_key' => 123])));
        $this->assertSame(456, ($joinStrategy->getInnerKeySelector())(new Model(['inner_key' => 456])));
        $this->assertEquals(
            new Model([
                'outer_key' => 123,
                'relation_key' => new \ArrayObject([
                    new Model(['inner_key' => 456]),
                    new Model(['inner_key' => 789]),
                ]),
            ]),
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

        $relationKeyName = 'relation_key';
        $oneToManyTableName = 'one_to_many_table';
        $oneToManyOuterKeyName = 'one_to_many_outer_key';
        $oneToManyInnerKeyName = 'one_to_many_inner_key';
        $manyToOneTableName = 'many_to_one_table';
        $manyToOneOuterKeyName = 'many_to_one_outer_key';
        $manyToOneInnerKeyName = 'many_to_one_inner_key';

        $grammar = $this->createMock(GrammarInterface::class);
        $queryBuilder = new SelectBuilder($grammar);
        $fetcher = $this->createMock(FetcherInterface::class);
        $fetcher
            ->expects($this->once())
            ->method('getClass')
            ->willReturn($innerClass);

        $collationClass = \ArrayObject::class;

        /** @var Relation<Model,Model,int,Model> */
        $relation = Relations::manyToMany(
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
        )($outerClass);
        $relationStrategy = $relation->getRelationStrategy();
        $joinStrategy = $relation->getJoinStrategy();

        $this->assertInstanceOf(Relation::class, $relation);
        $this->assertSame($outerClass, $relation->getResultClass());

        $this->assertInstanceOf(ManyTo::class, $relationStrategy);
        $this->assertSame($relationKeyName, $relationStrategy->getRelationKeyName());
        $this->assertSame($oneToManyTableName, $relationStrategy->getOneToManyTableName());
        $this->assertSame($oneToManyOuterKeyName, $relationStrategy->getOneToManyOuterKeyName());
        $this->assertSame($oneToManyInnerKeyName, $relationStrategy->getOneToManyInnerKeyName());
        $this->assertSame($manyToOneTableName, $relationStrategy->getManyToOneTableName());
        $this->assertSame($manyToOneOuterKeyName, $relationStrategy->getManyToOneOuterKeyName());
        $this->assertSame($manyToOneInnerKeyName, $relationStrategy->getManyToOneInnerKeyName());
        $this->assertSame($queryBuilder, $relationStrategy->getQueryBuilder());
        $this->assertSame($fetcher, $relationStrategy->getFetcher());

        $this->assertInstanceOf(GroupJoin::class, $joinStrategy);
        $this->assertSame(123, ($joinStrategy->getOuterKeySelector())(new Model(['one_to_many_outer_key' => 123])));
        $this->assertSame(456, ($joinStrategy->getInnerKeySelector())(new Model(['__pivot_one_to_many_inner_key' => 456])));
        $this->assertEquals(
            new Model([
                'outer_key' => 123,
                'relation_key' => new \ArrayObject([
                    new Model(['inner_key' => 456]),
                    new Model(['inner_key' => 789]),
                ]),
            ]),
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

        $relationKeyName = 'relation_key';
        $oneToManyTableName = 'one_to_many_table';
        $oneToManyOuterKeyName = 'one_to_many_outer_key';
        $oneToManyInnerKeyName = 'one_to_many_inner_key';
        $manyToOneTableName = 'many_to_one_table';
        $manyToOneOuterKeyName = 'many_to_one_outer_key';
        $manyToOneInnerKeyName = 'many_to_one_inner_key';
        $throughKeyName = 'through_key';

        $grammar = $this->createMock(GrammarInterface::class);
        $queryBuilder = new SelectBuilder($grammar);
        $fetcher = $this->createMock(FetcherInterface::class);
        $fetcher
            ->expects($this->once())
            ->method('getClass')
            ->willReturn($innerClass);

        /** @var Relation<Model,Model,int,Model> */
        $relation = Relations::throughManyToMany(
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
        )($outerClass);
        $relationStrategy = $relation->getRelationStrategy();
        $joinStrategy = $relation->getJoinStrategy();

        $this->assertInstanceOf(Relation::class, $relation);
        $this->assertSame($outerClass, $relation->getResultClass());

        $this->assertInstanceOf(ManyTo::class, $relationStrategy);
        $this->assertSame($relationKeyName, $relationStrategy->getRelationKeyName());
        $this->assertSame($oneToManyTableName, $relationStrategy->getOneToManyTableName());
        $this->assertSame($oneToManyOuterKeyName, $relationStrategy->getOneToManyOuterKeyName());
        $this->assertSame($oneToManyInnerKeyName, $relationStrategy->getOneToManyInnerKeyName());
        $this->assertSame($manyToOneTableName, $relationStrategy->getManyToOneTableName());
        $this->assertSame($manyToOneOuterKeyName, $relationStrategy->getManyToOneOuterKeyName());
        $this->assertSame($manyToOneInnerKeyName, $relationStrategy->getManyToOneInnerKeyName());
        $this->assertSame($queryBuilder, $relationStrategy->getQueryBuilder());
        $this->assertSame($fetcher, $relationStrategy->getFetcher());

        $this->assertInstanceOf(GroupJoin::class, $joinStrategy);
        $this->assertSame(123, ($joinStrategy->getOuterKeySelector())(new Model(['one_to_many_outer_key' => 123])));
        $this->assertSame(456, ($joinStrategy->getInnerKeySelector())(new Model(['__pivot_one_to_many_inner_key' => 456])));
        $this->assertEquals(
            new Model([
                'outer_key' => 123,
                'relation_key' => [
                    'foo',
                    'bar',
                ],
            ]),
            ($joinStrategy->getResultSelector())(
                new Model(['outer_key' => 123]),
                [new Model(['inner_key' => 456, 'through_key' => 'foo']), new Model(['inner_key' => 789, 'through_key' => 'bar'])]
            )
        );
    }

    public function testPolymorphic(): void
    {
        $outerClass = Model::class;

        $morphKey = 'morph_key';
        $relations = [
            'first' => $this->createMock(RelationInterface::class),
            'second' => $this->createMock(RelationInterface::class),
        ];
        $relationFactories = [
            'first' => $this->createMock(Spy::class),
            'second' => $this->createMock(Spy::class),
        ];

        foreach ($relationFactories as $key => $relationFactory) {
            $relationFactory
                ->expects($this->once())
                ->method('__invoke')
                ->with($this->identicalTo($outerClass))
                ->willReturn($relations[$key]);
        }

        /** @var array<string,callable(?class-string):RelationInterface<Model,Model>> $relationFactories */
        $relation = Relations::polymorphic($morphKey, $relationFactories)($outerClass);

        $this->assertInstanceOf(PolymorphicRelation::class, $relation);
        $this->assertSame($outerClass, $relation->getResultClass());
        $this->assertSame(123, ($relation->getMorphKeySelector())(new Model(['morph_key' => 123])));
        $this->assertSame($relations, $relation->getRelations());
    }
}
