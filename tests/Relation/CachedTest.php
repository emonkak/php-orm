<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\Relation;

use Emonkak\Orm\Relation\Cached;
use Emonkak\Orm\Relation\JoinStrategy\JoinStrategyInterface;
use Emonkak\Orm\Relation\RelationStrategyInterface;
use Emonkak\Orm\Tests\Fixtures\Model;
use Emonkak\Orm\Tests\QueryBuilderTestTrait;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;

/**
 * @covers \Emonkak\Orm\Relation\Cached
 */
class CachedTest extends TestCase
{
    use QueryBuilderTestTrait;

    public function testConstructor(): void
    {
        $innerRelationStrategy = $this->createMock(RelationStrategyInterface::class);

        $cache = $this->createMock(CacheInterface::class);
        $cacheKeySelector = function(int $id): string { return 'prefix.' . $id; };
        $cacheTtl = 3600;

        $relationStrategy = new Cached(
            $innerRelationStrategy,
            $cache,
            $cacheKeySelector,
            $cacheTtl
        );

        $this->assertSame($innerRelationStrategy, $relationStrategy->getRelationStrategy());
        $this->assertSame($cache, $relationStrategy->getCache());
        $this->assertSame($cacheKeySelector, $relationStrategy->getCacheKeySelector());
        $this->assertSame($cacheTtl, $relationStrategy->getCacheTtl());
    }

    public function testGetResult(): void
    {
        $outerKeys = [1, 2, 3];
        $expectedResult = [
            new Model(['id' => 1]),
            new Model(['id' => 2]),
            new Model(['id' => 3]),
        ];

        $cacheKeySelector = function(int $id): string { return 'model.' . $id; };
        $cacheTtl = 3600;

        $cache = $this->createMock(CacheInterface::class);
        $cache
            ->expects($this->once())
            ->method('getMultiple')
            ->with([
                'model.1',
                'model.2',
                'model.3',
            ])
            ->willReturn([
                'model.1' => $expectedResult[0],
                'model.2' => null,
                'model.3' => null,
            ]);
        $cache
            ->expects($this->once())
            ->method('setMultiple')
            ->with([
                'model.2' => $expectedResult[1],
                'model.3' => $expectedResult[2],
            ], $cacheTtl)
            ->willReturn(true);

        $innerRelationStrategy = $this->createMock(RelationStrategyInterface::class);
        $innerRelationStrategy
            ->expects($this->once())
            ->method('getResult')
            ->with([2, 3])
            ->willReturn([
                $expectedResult[1],
                $expectedResult[2],
            ]);

        $queryBuilder = $this->getSelectBuilder();

        $relationStrategy = new Cached(
            $innerRelationStrategy,
            $cache,
            $cacheKeySelector,
            $cacheTtl
        );

        $joinStrategy = $this->createMock(JoinStrategyInterface::class);
        $joinStrategy
            ->expects($this->once())
            ->method('getInnerKeySelector')
            ->with()
            ->willReturn(function(Model $model): int {
                return $model->id;
            });

        $result = $relationStrategy->getResult($outerKeys, $joinStrategy);

        $this->assertEquals($expectedResult, $result);
    }

    public function testGetResultFromOnlyCache(): void
    {
        $outerKeys = [1, 2, 3];
        $expectedResult = [
            new Model(['id' => 1]),
            new Model(['id' => 2]),
            new Model(['id' => 3]),
        ];

        $cacheKeySelector = function(int $id): string { return 'model.' . $id; };
        $cacheTtl = 3600;

        $cache = $this->createMock(CacheInterface::class);
        $cache
            ->expects($this->once())
            ->method('getMultiple')
            ->with([
                'model.1',
                'model.2',
                'model.3',
            ])
            ->willReturn([
                'model.1' => $expectedResult[0],
                'model.2' => $expectedResult[1],
                'model.3' => $expectedResult[2],
            ]);
        $cache
            ->expects($this->never())
            ->method('setMultiple');

        $innerRelationStrategy = $this->createMock(RelationStrategyInterface::class);
        $innerRelationStrategy
            ->expects($this->never())
            ->method('getResult');

        $queryBuilder = $this->getSelectBuilder();

        $relationStrategy = new Cached(
            $innerRelationStrategy,
            $cache,
            $cacheKeySelector,
            $cacheTtl
        );

        $joinStrategy = $this->createMock(JoinStrategyInterface::class);

        $result = $relationStrategy->getResult($outerKeys, $joinStrategy);

        $this->assertEquals($expectedResult, $result);
    }
}
