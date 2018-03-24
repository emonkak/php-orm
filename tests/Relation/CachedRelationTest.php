<?php

namespace Emonkak\Orm\Tests\Relation;

use Emonkak\Database\PDOInterface;
use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\Relation\CachedRelation;
use Emonkak\Orm\Relation\JoinStrategy\JoinStrategyInterface;
use Emonkak\Orm\Relation\JoinStrategy\OuterJoin;
use Emonkak\Orm\Relation\RelationInterface;
use Emonkak\Orm\Relation\StandardRelation;
use Emonkak\Orm\Relation\StandardRelationInterface;
use Emonkak\Orm\ResultSet\EmptyResultSet;
use Emonkak\Orm\ResultSet\PreloadResultSet;
use Emonkak\Orm\Tests\QueryBuilderTestTrait;
use Psr\SimpleCache\CacheInterface;

/**
 * @covers Emonkak\Orm\Relation\AbstractStandardRelation
 * @covers Emonkak\Orm\Relation\CachedRelation
 */
class CachedRelationTest extends \PHPUnit_Framework_TestCase
{
    use QueryBuilderTestTrait;

    public function testConstructor()
    {
        $pdo = $this->createMock(PDOInterface::class);
        $fetcher = $this->createMock(FetcherInterface::class);
        $builder = $this->createSelectBuilder();
        $joinStrategy = $this->createMock(JoinStrategyInterface::class);

        $innerRelation = $this->createMock(StandardRelationInterface::class);
        $innerRelation
            ->expects($this->once())
            ->method('getPdo')
            ->willReturn($pdo);
        $innerRelation
            ->expects($this->once())
            ->method('getFetcher')
            ->willReturn($fetcher);
        $innerRelation
            ->expects($this->once())
            ->method('getBuilder')
            ->willReturn($builder);
        $innerRelation
            ->expects($this->once())
            ->method('getJoinStrategy')
            ->willReturn($joinStrategy);

        $cache = $this->createMock(CacheInterface::class);
        $cachePrefix = 'prefix';
        $cacheTtl = 3600;

        $relation = new CachedRelation(
            $innerRelation,
            $cache,
            $cachePrefix,
            $cacheTtl
        );

        $this->assertSame($innerRelation, $relation->getInnerRelation());
        $this->assertSame($cache, $relation->getCache());
        $this->assertSame($cachePrefix, $relation->getCachePrefix());
        $this->assertSame($cacheTtl, $relation->getCacheTtl());
        $this->assertSame($pdo, $relation->getPdo());
        $this->assertSame($fetcher, $relation->getFetcher());
        $this->assertSame($builder, $relation->getBuilder());
        $this->assertSame($joinStrategy, $relation->getJoinStrategy());
    }

    public function testWith()
    {
        $cache = $this->createMock(CacheInterface::class);
        $cachePrefix = 'prefix';
        $cacheTtl = 3600;

        $childRelation1 = $this->createMock(RelationInterface::class);
        $childRelation2 = $this->createMock(RelationInterface::class);

        $innerRelation = $this->createMock(StandardRelationInterface::class);
        $innerRelation
            ->expects($this->at(0))
            ->method('with')
            ->with($this->identicalTo($childRelation1))
            ->willReturn($innerRelation);
        $innerRelation
            ->expects($this->at(1))
            ->method('with')
            ->with($this->identicalTo($childRelation2))
            ->willReturn($innerRelation);

        $relation = (new CachedRelation(
                $innerRelation,
                $cache,
                $cachePrefix,
                $cacheTtl
            ))
            ->with($childRelation1)
            ->with($childRelation2);

        $this->assertInstanceOf(CachedRelation::class, $relation);
    }

    public function testAssociate()
    {
        $outerElements = [
            (object) ['page_id' => 1, 'revision_id' => 4, 'title' => 'foo', 'version' => 10],
            (object) ['page_id' => 2, 'revision_id' => 5, 'title' => 'bar', 'version' => 20],
            (object) ['page_id' => 3, 'revision_id' => 6, 'title' => 'baz', 'version' => 30],
            (object) ['page_id' => 4, 'revision_id' => 6, 'title' => 'baz', 'version' => 40]
        ];
        $innerElements = [
            (object) ['revision_id' => 4, 'content' => 'foo'],
            (object) ['revision_id' => 5, 'content' => 'bar'],
            (object) ['revision_id' => 6, 'content' => 'baz']
        ];
        $expectedResult = [
            (object) [
                'page_id' => 1,
                'revision_id' => 4,
                'title' => 'foo',
                'version' => 10,
                'revision' => (object) ['revision_id' => 4, 'content' => 'foo']
            ],
            (object) [
                'page_id' => 2,
                'revision_id' => 5,
                'title' => 'bar',
                'version' => 20,
                'revision' => (object) ['revision_id' => 5, 'content' => 'bar']
            ],
            (object) [
                'page_id' => 3,
                'revision_id' => 6,
                'title' => 'baz',
                'version' => 30,
                'revision' => (object) ['revision_id' => 6, 'content' => 'baz']
            ],
            (object) [
                'page_id' => 4,
                'revision_id' => 6,
                'title' => 'baz',
                'version' => 40,
                'revision' => (object) ['revision_id' => 6, 'content' => 'baz']
            ]
        ];

        $cachePrefix = 'revision:';
        $cacheTtl = 3600;

        $cache = $this->createMock(CacheInterface::class);
        $cache
            ->expects($this->once())
            ->method('getMultiple')
            ->with([
                'revision:4',
                'revision:5',
                'revision:6'
            ])
            ->willReturn([
                'revision:4' => null,
                'revision:5' => $innerElements[1],
                'revision:6' => null
            ]);
        $cache
            ->expects($this->once())
            ->method('setMultiple')
            ->with([
                'revision:4' => $innerElements[0],
                'revision:6' => $innerElements[2]
            ], $cacheTtl)
            ->willReturn(true);

        $stmt = $this->createMock(PDOStatementInterface::class);
        $stmt
            ->expects($this->exactly(2))
            ->method('bindValue')
            ->withConsecutive(
                [1, 4, \PDO::PARAM_STR],
                [2, 6, \PDO::PARAM_STR]
            )
            ->willReturn(true);

        $pdo = $this->createMock(PDOInterface::class);
        $pdo
            ->expects($this->once())
            ->method('prepare')
            ->with('SELECT * FROM `revisions` WHERE (`revisions`.`revision_id` IN (?, ?))')
            ->willReturn($stmt);

        $fetcher = $this->createMock(FetcherInterface::class);
        $fetcher
            ->expects($this->once())
            ->method('fetch')
            ->with($this->identicalTo($stmt))
            ->willReturn(new PreloadResultSet([$innerElements[0], $innerElements[2]], null));

        $builder = $this->createSelectBuilder();
        $joinStrategy = new OuterJoin();

        $relation = new CachedRelation(
            new StandardRelation(
                'revision',
                'revisions',
                'revision_id',
                'revision_id',
                $pdo,
                $fetcher,
                $builder,
                $joinStrategy
            ),
            $cache,
            $cachePrefix,
            $cacheTtl
        );

        $result = $relation->associate(new PreloadResultSet($outerElements, null));
        $this->assertEquals($expectedResult, iterator_to_array($result));
    }

    public function testAssociateEmpty()
    {
        $pdo = $this->createMock(PDOInterface::class);
        $fetcher = $this->createMock(FetcherInterface::class);
        $builder = $this->createSelectBuilder();
        $joinStrategy = new OuterJoin();

        $cache = $this->createMock(CacheInterface::class);
        $cacheKeySelector = function($element, $key) {
            return $key;
        };
        $cacheTtl = 3600;

        $relation = new CachedRelation(
            new StandardRelation(
                'revision',
                'revisions',
                'revision_id',
                'revision_id',
                $pdo,
                $fetcher,
                $builder,
                $joinStrategy
            ),
            $cache,
            $cacheKeySelector,
            $cacheTtl
        );

        $result = $relation->associate(new EmptyResultSet(null));
        $this->assertEmpty(iterator_to_array($result));
    }
}
