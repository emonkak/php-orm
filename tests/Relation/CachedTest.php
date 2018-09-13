<?php

namespace Emonkak\Orm\Tests\Relation;

use Emonkak\Database\PDOInterface;
use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\Relation\Cached;
use Emonkak\Orm\Relation\RelationInterface;
use Emonkak\Orm\Relation\RelationStrategyInterface;
use Emonkak\Orm\ResultSet\PreloadedResultSet;
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
        $cacheKeySelector = function($key) { return 'prefix.' . $key; };
        $cacheTtl = 3600;

        $relationStrategy = new Cached(
            $innerRelationStrategy,
            $cache,
            $cacheKeySelector,
            $cacheTtl
        );

        $this->assertSame($innerRelationStrategy, $relationStrategy->getInnerRelationStrategy());
        $this->assertSame($cache, $relationStrategy->getCache());
        $this->assertSame($cacheKeySelector, $relationStrategy->getCacheKeySelector());
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

        $cacheKeySelector = function($key) { return 'model.' . $key; };
        $cacheTtl = 3600;

        $cache = $this->createMock(CacheInterface::class);
        $cache
            ->expects($this->once())
            ->method('getMultiple')
            ->with([
                'model.1',
                'model.2',
                'model.3'
            ])
            ->willReturn([
                'model.1' => $expectedResult[0],
                'model.2' => null,
                'model.3' => null
            ]);
        $cache
            ->expects($this->once())
            ->method('setMultiple')
            ->with([
                'model.2' => $expectedResult[1],
                'model.3' => $expectedResult[2]
            ], $cacheTtl)
            ->willReturn(true);

        $innerRelationStrategy = $this->createMock(RelationStrategyInterface::class);
        $innerRelationStrategy
            ->expects($this->once())
            ->method('getResult')
            ->with([2, 3])
            ->willReturn(new PreloadedResultSet(
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

        $builder = $this->getSelectBuilder();

        $relationStrategy = new Cached(
            $innerRelationStrategy,
            $cache,
            $cacheKeySelector,
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

        $cacheKeySelector = function($key) { return 'model.' . $key; };
        $cacheTtl = 3600;

        $cache = $this->createMock(CacheInterface::class);
        $cache
            ->expects($this->once())
            ->method('getMultiple')
            ->with([
                'model.1',
                'model.2',
                'model.3'
            ])
            ->willReturn([
                'model.1' => $expectedResult[0],
                'model.2' => $expectedResult[1],
                'model.3' => $expectedResult[2]
            ]);
        $cache
            ->expects($this->never())
            ->method('setMultiple');

        $innerRelationStrategy = $this->createMock(RelationStrategyInterface::class);
        $innerRelationStrategy
            ->expects($this->never())
            ->method('getResult');

        $builder = $this->getSelectBuilder();

        $relationStrategy = new Cached(
            $innerRelationStrategy,
            $cache,
            $cacheKeySelector,
            $cacheTtl
        );

        $result = $relationStrategy->getResult($outerKeys);

        $this->assertEquals($expectedResult, $result->toArray());
        $this->assertSame(Model::class, $result->getClass());
    }

    public function testSelectorResolvings()
    {
        $pdo = $this->createMock(PDOInterface::class);
        $fetcher = $this->createMock(FetcherInterface::class);
        $builder = $this->getSelectBuilder();

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
        $cacheKeySelector = function($key) { return 'prefix.' . $key; };
        $cacheTtl = 3600;

        $relationStrategy = new Cached(
            $innerRelationStrategy,
            $cache,
            $cacheKeySelector,
            $cacheTtl
        );

        $this->assertSame($outerKeySelector, $relationStrategy->getOuterKeySelector($outerClass));
        $this->assertSame($innerKeySelector, $relationStrategy->getInnerKeySelector($innerClass));
        $this->assertSame($resultSelector, $relationStrategy->getResultSelector($outerClass, $innerClass));
    }
}
