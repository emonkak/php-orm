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
use Emonkak\Orm\ResultSet\PreloadResultSet;
use Emonkak\Orm\SelectBuilder;
use Psr\SimpleCache\CacheInterface;

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
        $cache = $this->createMock(CacheInterface::class);
        $joinStrategy = $this->createMock(JoinStrategyInterface::class);

        $relation = new CachedRelation(
            'relation_key',
            'table',
            'outer_key',
            'inner_key',
            $pdo,
            $fetcher,
            $cache,
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
        $this->assertSame($cache, $relation->getCache());
        $this->assertSame('cache_prefix', $relation->getCachePrefix());
        $this->assertSame(3600, $relation->getCacheTtl());
        $this->assertSame($builder, $relation->getBuilder());
        $this->assertSame($joinStrategy, $relation->getJoinStrategy());
    }

    public function testWith()
    {
        $pdo = $this->createMock(PDOInterface::class);
        $fetcher = $this->createMock(FetcherInterface::class);
        $builder = new SelectBuilder();
        $cache = $this->createMock(CacheInterface::class);
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
                $cache,
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

    public function testAssociate()
    {
        $outerElements = [
            (object) ['user_id' => 1, 'name' => 'foo'],
            (object) ['user_id' => 2, 'name' => 'bar'],
            (object) ['user_id' => 3, 'name' => 'baz'],
        ];
        $innerElements = [
            (object) ['post_id' => 1, 'user_id' => 1, 'content' => 'foo'],
            (object) ['post_id' => 2, 'user_id' => 1, 'content' => 'bar'],
            (object) ['post_id' => 3, 'user_id' => 3, 'content' => 'baz'],
        ];
        $expectedResult = [
            (object) [
                'user_id' => 1,
                'name' => 'foo',
                'posts' => [
                    (object) ['post_id' => 2, 'user_id' => 1, 'content' => 'bar'],
                    (object) ['post_id' => 1, 'user_id' => 1, 'content' => 'foo'],
                ],
            ],
            (object) [
                'user_id' => 2,
                'name' => 'bar',
                'posts' => [],
            ],
            (object) [
                'user_id' => 3,
                'name' => 'baz',
                'posts' => [
                    (object) ['post_id' => 3, 'user_id' => 3, 'content' => 'baz'],
                ],
            ],
        ];

        $stmt = $this->createMock(PDOStatementInterface::class);
        $stmt
            ->expects($this->exactly(2))
            ->method('bindValue')
            ->withConsecutive(
                [1, '1', \PDO::PARAM_STR],
                [2, '3', \PDO::PARAM_STR]
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
            ->willReturn(new PreloadResultSet([$innerElements[0], $innerElements[2]], null));

        $cache = $this->createMock(CacheInterface::class);
        $cache
            ->expects($this->once())
            ->method('getMultiple')
            ->with(['cache-prefix-1', 'cache-prefix-2', 'cache-prefix-3'])
            ->willReturn([
                'cache-prefix-1' => null,
                'cache-prefix-2' => $innerElements[1],
                'cache-prefix-3' => null,
            ]);
        $cache
            ->expects($this->once())
            ->method('setMultiple')
            ->with([
                'cache-prefix-1' => $innerElements[0],
                'cache-prefix-3' => $innerElements[2],
            ], 3600)
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
            $cache,
            'cache-prefix-',
            3600,
            $builder,
            $joinStrategy
        );

        $result = $relation->associate(new PreloadResultSet($outerElements, null));
        $this->assertEquals($expectedResult, iterator_to_array($result));
    }

    public function testAssociateEmpty()
    {
        $pdo = $this->createMock(PDOInterface::class);
        $fetcher = $this->createMock(FetcherInterface::class);
        $cache = $this->createMock(CacheInterface::class);
        $builder = new SelectBuilder();
        $joinStrategy = new GroupJoin();

        $relation = new CachedRelation(
            'posts',
            'posts',
            'user_id',
            'user_id',
            $pdo,
            $fetcher,
            $cache,
            'cache-prefix-',
            3600,
            $builder,
            $joinStrategy
        );

        $result = $relation->associate(new EmptyResultSet(null));
        $this->assertEmpty(iterator_to_array($result));
    }
}
