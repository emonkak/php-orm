<?php

namespace Emonkak\Orm\Tests\Relation;

use Emonkak\Database\PDOInterface;
use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\Relation\CachedRelation;
use Emonkak\Orm\Relation\JoinStrategy\GroupJoin;
use Emonkak\Orm\Relation\JoinStrategy\JoinStrategyInterface;
use Emonkak\Orm\Relation\RelationInterface;
use Emonkak\Orm\ResultSet\EmptyResultSet;
use Emonkak\Orm\ResultSet\FrozenResultSet;
use Emonkak\Orm\SelectBuilder;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

/**
 * @covers Emonkak\Orm\Relation\AbstractRelation
 * @covers Emonkak\Orm\Relation\CachedRelation
 */
class CachedRelationTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $pdo = $this->createMock(PDOInterface::class);
        $fetcher = $this->createMock(FetcherInterface::class);
        $builder = new SelectBuilder();
        $cachePool = $this->createMock(CacheItemPoolInterface::class);
        $joinStrategy = $this->createMock(JoinStrategyInterface::class);

        $relation = new CachedRelation(
            'relation_key',
            'table',
            'outer_key',
            'inner_key',
            $pdo,
            $fetcher,
            $cachePool,
            'cache_prefix',
            3600,
            $builder,
            $joinStrategy
        );

        $this->assertSame('relation_key', $relation->getRelationKey());
        $this->assertSame('table', $relation->getTable());
        $this->assertSame('outer_key', $relation->getOuterKey());
        $this->assertSame('inner_key', $relation->getInnerKey());
        $this->assertSame($pdo, $relation->getPdo());
        $this->assertSame($fetcher, $relation->getFetcher());
        $this->assertSame($cachePool, $relation->getCachePool());
        $this->assertSame('cache_prefix', $relation->getCachePrefix());
        $this->assertSame(3600, $relation->getCacheLifetime());
        $this->assertSame($builder, $relation->getBuilder());
        $this->assertSame($joinStrategy, $relation->getJoinStrategy());
    }

    public function testWith()
    {
        $pdo = $this->createMock(PDOInterface::class);
        $fetcher = $this->createMock(FetcherInterface::class);
        $builder = new SelectBuilder();
        $cachePool = $this->createMock(CacheItemPoolInterface::class);
        $joinStrategy = $this->createMock(JoinStrategyInterface::class);

        $childRelation1 = $this->createMock(RelationInterface::class);
        $childRelation2 = $this->createMock(RelationInterface::class);

        $relation = (new CachedRelation(
                'relation_key',
                'table',
                'outer_key',
                'inner_key',
                $pdo,
                $fetcher,
                $cachePool,
                'cache_prefix',
                3600,
                $builder,
                $joinStrategy
            ))
            ->with($childRelation1)
            ->with($childRelation2);

        $this->assertInstanceOf(CachedRelation::class, $relation);
        $this->assertEquals([$childRelation1, $childRelation2], $relation->getBuilder()->getRelations());
    }

    public function testJoin()
    {
        $outerElements = [
            ['user_id' => 1, 'name' => 'foo'],
            ['user_id' => 2, 'name' => 'bar'],
            ['user_id' => 3, 'name' => 'baz'],
        ];
        $innerElements = [
            ['post_id' => 1, 'user_id' => 1, 'content' => 'foo'],
            ['post_id' => 2, 'user_id' => 1, 'content' => 'bar'],
            ['post_id' => 3, 'user_id' => 3, 'content' => 'baz'],
        ];
        $expectedResult = [
            [
                'user_id' => 1,
                'name' => 'foo',
                'posts' => [
                    ['post_id' => 2, 'user_id' => 1, 'content' => 'bar'],
                    ['post_id' => 1, 'user_id' => 1, 'content' => 'foo'],
                ],
            ],
            [
                'user_id' => 2,
                'name' => 'bar',
                'posts' => [],
            ],
            [
                'user_id' => 3,
                'name' => 'baz',
                'posts' => [
                    ['post_id' => 3, 'user_id' => 3, 'content' => 'baz'],
                ],
            ],
        ];

        $stmt = $this->createMock(PDOStatementInterface::class);
        $stmt
            ->expects($this->exactly(2))
            ->method('bindValue')
            ->withConsecutive(
                [1, 1, \PDO::PARAM_INT],
                [2, 3, \PDO::PARAM_INT]
            )
            ->willReturn(true);

        $pdo = $this->createMock(PDOInterface::class);
        $pdo
            ->expects($this->once())
            ->method('prepare')
            ->with('SELECT * FROM `posts` WHERE (`posts`.`user_id` IN (?, ?))')
            ->willReturn($stmt);

        $fetcher = $this->createMock(FetcherInterface::class);
        $fetcher
            ->expects($this->once())
            ->method('fetch')
            ->with($this->identicalTo($stmt))
            ->willReturn(new FrozenResultSet([$innerElements[0], $innerElements[2]], null));

        $cacheItems = [
            $this->createMock(CacheItemInterface::class),
            $this->createMock(CacheItemInterface::class),
            $this->createMock(CacheItemInterface::class),
        ];

        $cacheItems[0]
            ->expects($this->once())
            ->method('isHit')
            ->willReturn(false);
        $cacheItems[0]
            ->expects($this->any())
            ->method('getKey')
            ->willReturn('cache-prefix-1');
        $cacheItems[0]
            ->expects($this->once())
            ->method('set')
            ->with($innerElements[0])
            ->will($this->returnSelf());
        $cacheItems[0]
            ->expects($this->once())
            ->method('expiresAfter')
            ->with(3600)
            ->will($this->returnSelf());

        $cacheItems[1]
            ->expects($this->once())
            ->method('isHit')
            ->willReturn(true);
        $cacheItems[1]
            ->expects($this->any())
            ->method('getKey')
            ->willReturn('cache-prefix-2');
        $cacheItems[1]
            ->expects($this->once())
            ->method('get')
            ->willReturn($innerElements[1]);

        $cacheItems[2]
            ->expects($this->once())
            ->method('isHit')
            ->willReturn(false);
        $cacheItems[2]
            ->expects($this->any())
            ->method('getKey')
            ->willReturn('cache-prefix-3');
        $cacheItems[2]
            ->expects($this->once())
            ->method('set')
            ->with($innerElements[2])
            ->will($this->returnSelf());
        $cacheItems[2]
            ->expects($this->once())
            ->method('expiresAfter')
            ->with(3600)
            ->will($this->returnSelf());

        $cachePool = $this->createMock(CacheItemPoolInterface::class);
        $cachePool
            ->expects($this->once())
            ->method('getItems')
            ->with(['cache-prefix-1', 'cache-prefix-2', 'cache-prefix-3'])
            ->willReturn($cacheItems);
        $cachePool
            ->expects($this->exactly(2))
            ->method('saveDeferred')
            ->withConsecutive(
                [$cacheItems[0]],
                [$cacheItems[2]]
            )
            ->willReturn(true);
        $cachePool
            ->expects($this->once())
            ->method('commit')
            ->willReturn(true);

        $builder = new SelectBuilder();
        $joinStrategy = new GroupJoin();

        $relation = new CachedRelation(
            'posts',
            'posts',
            'user_id',
            'user_id',
            $pdo,
            $fetcher,
            $cachePool,
            'cache-prefix-',
            3600,
            $builder,
            $joinStrategy
        );

        $result = $relation->join(new FrozenResultSet($outerElements, null));
        $this->assertEquals($expectedResult, iterator_to_array($result));
    }

    public function testJoinEmpty()
    {
        $pdo = $this->createMock(PDOInterface::class);
        $fetcher = $this->createMock(FetcherInterface::class);
        $cachePool = $this->createMock(CacheItemPoolInterface::class);
        $builder = new SelectBuilder();
        $joinStrategy = new GroupJoin();

        $relation = new CachedRelation(
            'posts',
            'posts',
            'user_id',
            'user_id',
            $pdo,
            $fetcher,
            $cachePool,
            'cache-prefix-',
            3600,
            $builder,
            $joinStrategy
        );

        $result = $relation->join(new EmptyResultSet(null));
        $this->assertEmpty(iterator_to_array($result));
    }
}
