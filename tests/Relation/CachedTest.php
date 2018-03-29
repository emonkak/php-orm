<?php

namespace Emonkak\Orm\Tests\Relation;

use Emonkak\Database\PDOInterface;
use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\Relation\Cached;
use Emonkak\Orm\Relation\RelationInterface;
use Emonkak\Orm\Relation\RelationStrategyInterface;
use Emonkak\Orm\ResultSet\PreloadResultSet;
use Emonkak\Orm\Tests\Fixtures\Model;
use Emonkak\Orm\Tests\QueryBuilderTestTrait;
use Psr\SimpleCache\CacheInterface;

/**
 * @covers Emonkak\Orm\Relation\Cached
 */
class CachedTest extends \PHPUnit_Framework_TestCase
{
    use QueryBuilderTestTrait;

    public function testConstructor()
    {
        $outerKeySelector = function() {};
        $innerKeySelector = function() {};
        $resultSelector = function() {};

        $innerRelationStrategy = $this->createMock(RelationStrategyInterface::class);

        $cache = $this->createMock(CacheInterface::class);
        $cachePrefix = 'prefix';
        $cacheTtl = 3600;

        $relationStrategy = new Cached(
            $innerRelationStrategy,
            $cache,
            $cachePrefix,
            $cacheTtl
        );

        $this->assertSame($innerRelationStrategy, $relationStrategy->getInnerRelationStrategy());
        $this->assertSame($cache, $relationStrategy->getCache());
        $this->assertSame($cachePrefix, $relationStrategy->getCachePrefix());
        $this->assertSame($cacheTtl, $relationStrategy->getCacheTtl());
    }

    public function testGetResult()
    {
        $outerKeys = [1, 2, 3];
        $expectedResult = [
            new Model(['id' => 1]),
            new Model(['id' => 2]),
            new Model(['id' => 3])
        ];

        $cachePrefix = 'model:';
        $cacheTtl = 3600;

        $cache = $this->createMock(CacheInterface::class);
        $cache
            ->expects($this->once())
            ->method('getMultiple')
            ->with([
                'model:1',
                'model:2',
                'model:3'
            ])
            ->willReturn([
                'model:1' => $expectedResult[0],
                'model:2' => null,
                'model:3' => null
            ]);
        $cache
            ->expects($this->once())
            ->method('setMultiple')
            ->with([
                'model:2' => $expectedResult[1],
                'model:3' => $expectedResult[2]
            ], $cacheTtl)
            ->willReturn(true);

        $innerRelationStrategy = $this->createMock(RelationStrategyInterface::class);
        $innerRelationStrategy
            ->expects($this->once())
            ->method('getResult')
            ->with([2, 3])
            ->willReturn(new PreloadResultSet(
                [
                    $expectedResult[1],
                    $expectedResult[2]
                ],
                Model::class
            ));
        $innerRelationStrategy
            ->expects($this->once())
            ->method('getInnerKeySelector')
            ->with(Model::class)
            ->willReturn(function($model) {
                return $model->id;
            });

        $builder = $this->createSelectBuilder();

        $relationStrategy = new Cached(
            $innerRelationStrategy,
            $cache,
            $cachePrefix,
            $cacheTtl
        );

        $result = $relationStrategy->getResult($outerKeys);

        $this->assertEquals($expectedResult, $result->toArray());
        $this->assertSame(Model::class, $result->getClass());
    }

    public function testGetResultFromOnlyCache()
    {
        $outerKeys = [1, 2, 3];
        $expectedResult = [
            new Model(['id' => 1]),
            new Model(['id' => 2]),
            new Model(['id' => 3])
        ];

        $cachePrefix = 'model:';
        $cacheTtl = 3600;

        $cache = $this->createMock(CacheInterface::class);
        $cache
            ->expects($this->once())
            ->method('getMultiple')
            ->with([
                'model:1',
                'model:2',
                'model:3'
            ])
            ->willReturn([
                'model:1' => $expectedResult[0],
                'model:2' => $expectedResult[1],
                'model:3' => $expectedResult[2]
            ]);
        $cache
            ->expects($this->never())
            ->method('setMultiple');

        $innerRelationStrategy = $this->createMock(RelationStrategyInterface::class);
        $innerRelationStrategy
            ->expects($this->never())
            ->method('getResult');

        $builder = $this->createSelectBuilder();

        $relationStrategy = new Cached(
            $innerRelationStrategy,
            $cache,
            $cachePrefix,
            $cacheTtl
        );

        $result = $relationStrategy->getResult($outerKeys);

        $this->assertEquals($expectedResult, $result->toArray());
        $this->assertSame(Model::class, $result->getClass());
    }

    public function testWith()
    {
        $cache = $this->createMock(CacheInterface::class);
        $cachePrefix = 'prefix';
        $cacheTtl = 3600;

        $childRelation1 = $this->createMock(RelationInterface::class);
        $childRelation2 = $this->createMock(RelationInterface::class);

        $innerRelationStrategy1 = $this->createMock(RelationStrategyInterface::class);
        $innerRelationStrategy2 = $this->createMock(RelationStrategyInterface::class);
        $innerRelationStrategy3 = $this->createMock(RelationStrategyInterface::class);

        $innerRelationStrategy1
            ->expects($this->once())
            ->method('with')
            ->with($this->identicalTo($childRelation1))
            ->willReturn($innerRelationStrategy2);
        $innerRelationStrategy2
            ->expects($this->once())
            ->method('with')
            ->with($this->identicalTo($childRelation2))
            ->willReturn($innerRelationStrategy3);

        $relationStrategy = new Cached(
            $innerRelationStrategy1,
            $cache,
            $cachePrefix,
            $cacheTtl
        );

        $newRelationStrategy = $relationStrategy
            ->with($childRelation1)
            ->with($childRelation2);

        $this->assertInstanceOf(Cached::class, $newRelationStrategy);
        $this->assertNotSame($relationStrategy, $newRelationStrategy);
        $this->assertSame($innerRelationStrategy3, $newRelationStrategy->getInnerRelationStrategy());
    }

    public function testSelectorResolvings()
    {
        $pdo = $this->createMock(PDOInterface::class);
        $fetcher = $this->createMock(FetcherInterface::class);
        $builder = $this->createSelectBuilder();

        $outerClass = 'OuterClass';
        $innerClass = 'InnerClass';

        $outerKeySelector = function() {};
        $innerKeySelector = function() {};
        $resultSelector = function() {};

        $innerRelationStrategy = $this->createMock(RelationStrategyInterface::class);
        $innerRelationStrategy
            ->expects($this->once())
            ->method('getOuterKeySelector')
            ->with($outerClass)
            ->willReturn($outerKeySelector);
        $innerRelationStrategy
            ->expects($this->once())
            ->method('getInnerKeySelector')
            ->with($innerClass)
            ->willReturn($innerKeySelector);
        $innerRelationStrategy
            ->expects($this->once())
            ->method('getResultSelector')
            ->with($outerClass, $innerClass)
            ->willReturn($resultSelector);

        $cache = $this->createMock(CacheInterface::class);
        $cachePrefix = 'prefix';
        $cacheTtl = 3600;

        $relationStrategy = new Cached(
            $innerRelationStrategy,
            $cache,
            $cachePrefix,
            $cacheTtl
        );

        $this->assertSame($outerKeySelector, $relationStrategy->getOuterKeySelector($outerClass));
        $this->assertSame($innerKeySelector, $relationStrategy->getInnerKeySelector($innerClass));
        $this->assertSame($resultSelector, $relationStrategy->getResultSelector($outerClass, $innerClass));
    }
}