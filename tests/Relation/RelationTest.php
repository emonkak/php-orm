<?php

namespace Emonkak\Orm\Tests\Relation;

use Emonkak\Database\PDOInterface;
use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\Relation\JoinStrategy\GroupJoin;
use Emonkak\Orm\Relation\JoinStrategy\JoinStrategyInterface;
use Emonkak\Orm\Relation\Relation;
use Emonkak\Orm\Relation\RelationInterface;
use Emonkak\Orm\ResultSet\EmptyResultSet;
use Emonkak\Orm\ResultSet\FrozenResultSet;
use Emonkak\Orm\SelectBuilder;

/**
 * @covers Emonkak\Orm\Relation\AbstractRelation
 * @covers Emonkak\Orm\Relation\Relation
 */
class RelationTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $pdo = $this->createMock(PDOInterface::class);
        $fetcher = $this->createMock(FetcherInterface::class);
        $builder = new SelectBuilder();
        $joinStrategy = $this->createMock(JoinStrategyInterface::class);

        $relation = new Relation(
            'relation_key',
            'table',
            'outer_key',
            'inner_key',
            $pdo,
            $fetcher,
            $builder,
            $joinStrategy
        );

        $this->assertSame('relation_key', $relation->getRelationKey());
        $this->assertSame('table', $relation->getTable());
        $this->assertSame('outer_key', $relation->getOuterKey());
        $this->assertSame('inner_key', $relation->getInnerKey());
        $this->assertSame($pdo, $relation->getPdo());
        $this->assertSame($fetcher, $relation->getFetcher());
        $this->assertSame($builder, $relation->getBuilder());
        $this->assertSame($joinStrategy, $relation->getJoinStrategy());
    }

    public function testWith()
    {
        $pdo = $this->createMock(PDOInterface::class);
        $fetcher = $this->createMock(FetcherInterface::class);
        $builder = new SelectBuilder();
        $joinStrategy = $this->createMock(JoinStrategyInterface::class);

        $childRelation1 = $this->createMock(RelationInterface::class);
        $childRelation2 = $this->createMock(RelationInterface::class);

        $relation = (new Relation(
                'relation_key',
                'table',
                'outer_key',
                'inner_key',
                $pdo,
                $fetcher,
                $builder,
                $joinStrategy
            ))
            ->with($childRelation1)
            ->with($childRelation2);

        $this->assertInstanceOf(Relation::class, $relation);
        $this->assertEquals([$childRelation1, $childRelation2], $relation->getBuilder()->getRelations());
    }

    public function testAssociate()
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
                    ['post_id' => 1, 'user_id' => 1, 'content' => 'foo'],
                    ['post_id' => 2, 'user_id' => 1, 'content' => 'bar'],
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
            ->expects($this->exactly(3))
            ->method('bindValue')
            ->withConsecutive(
                [1, 1, \PDO::PARAM_INT],
                [2, 2, \PDO::PARAM_INT],
                [3, 3, \PDO::PARAM_INT]
            )
            ->willReturn(true);

        $pdo = $this->createMock(PDOInterface::class);
        $pdo
            ->expects($this->once())
            ->method('prepare')
            ->with('SELECT * FROM `posts` WHERE (`posts`.`user_id` IN (?, ?, ?))')
            ->willReturn($stmt);

        $fetcher = $this->createMock(FetcherInterface::class);
        $fetcher
            ->expects($this->once())
            ->method('fetch')
            ->with($this->identicalTo($stmt))
            ->willReturn(new FrozenResultSet($innerElements, null));

        $builder = new SelectBuilder();
        $joinStrategy = new GroupJoin();

        $relation = new Relation(
            'posts',
            'posts',
            'user_id',
            'user_id',
            $pdo,
            $fetcher,
            $builder,
            $joinStrategy
        );

        $result = $relation->associate(new FrozenResultSet($outerElements, null));
        $this->assertEquals($expectedResult, iterator_to_array($result));
    }

    public function testAssociateEmpty()
    {
        $stmt = $this->createMock(PDOStatementInterface::class);
        $pdo = $this->createMock(PDOInterface::class);
        $fetcher = $this->createMock(FetcherInterface::class);
        $builder = new SelectBuilder();
        $joinStrategy = new GroupJoin();

        $relation = new Relation(
            'posts',
            'posts',
            'user_id',
            'user_id',
            $pdo,
            $fetcher,
            $builder,
            $joinStrategy
        );

        $result = $relation->associate(new EmptyResultSet(null));
        $this->assertEmpty(iterator_to_array($result));
    }
}
