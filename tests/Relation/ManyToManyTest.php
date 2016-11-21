<?php

namespace Emonkak\Orm\Tests\Relation;

use Emonkak\Database\PDOInterface;
use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\Relation\JoinStrategy\GroupJoin;
use Emonkak\Orm\Relation\JoinStrategy\JoinStrategyInterface;
use Emonkak\Orm\Relation\ManyToMany;
use Emonkak\Orm\Relation\RelationInterface;
use Emonkak\Orm\ResultSet\EmptyResultSet;
use Emonkak\Orm\ResultSet\FrozenResultSet;
use Emonkak\Orm\SelectBuilder;

/**
 * @covers Emonkak\Orm\Relation\ManyToMany
 */
class ManyToManyTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $pdo = $this->getMock(PDOInterface::class);
        $fetcher = $this->getMock(FetcherInterface::class);
        $builder = new SelectBuilder();
        $joinStrategy = $this->getMock(JoinStrategyInterface::class);

        $relation = new ManyToMany(
            'relation_key',
            'one_to_many_table',
            'one_to_many_outer_key',
            'one_to_many_inner_key',
            'many_to_one_table',
            'many_to_one_outer_key',
            'many_to_one_inner_key',
            $pdo,
            $fetcher,
            $builder,
            $joinStrategy
        );

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
        $this->assertSame($joinStrategy, $relation->getJoinStrategy());
    }

    public function testWith()
    {
        $pdo = $this->getMock(PDOInterface::class);
        $fetcher = $this->getMock(FetcherInterface::class);
        $builder = new SelectBuilder();
        $joinStrategy = $this->getMock(JoinStrategyInterface::class);

        $childRelation1 = $this->getMock(RelationInterface::class);
        $childRelation2 = $this->getMock(RelationInterface::class);

        $relation = (new ManyToMany(
                'relation_key',
                'one_to_many_table',
                'one_to_many_outer_key',
                'one_to_many_inner_key',
                'many_to_one_table',
                'many_to_one_outer_key',
                'many_to_one_inner_key',
                $pdo,
                $fetcher,
                $builder,
                $joinStrategy
            ))
            ->with($childRelation1)
            ->with($childRelation2);

        $this->assertInstanceOf(ManyToMany::class, $relation);
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
            ['__pivot_user_id' => 1, 'user_id' => 2, 'name' => 'bar'],
            ['__pivot_user_id' => 1, 'user_id' => 3, 'name' => 'baz'],
            ['__pivot_user_id' => 2, 'user_id' => 1, 'name' => 'foo'],
            ['__pivot_user_id' => 3, 'user_id' => 2, 'name' => 'bar'],
        ];
        $expectedResult = [
            [
                'user_id' => 1,
                'name' => 'foo',
                'friends' => [
                    ['user_id' => 2, 'name' => 'bar'],
                    ['user_id' => 3, 'name' => 'baz'],
                ],
            ],
            [
                'user_id' => 2,
                'name' => 'bar',
                'friends' => [
                    ['user_id' => 1, 'name' => 'foo'],
                ],
            ],
            [
                'user_id' => 3,
                'name' => 'baz',
                'friends' => [
                    ['user_id' => 2, 'name' => 'bar'],
                ],
            ],
        ];

        $stmt = $this->getMock(PDOStatementInterface::class);
        $stmt
            ->expects($this->exactly(3))
            ->method('bindValue')
            ->withConsecutive(
                [1, 1, \PDO::PARAM_INT],
                [2, 2, \PDO::PARAM_INT],
                [3, 3, \PDO::PARAM_INT]
            )
            ->willReturn(true);

        $pdo = $this->getMock(PDOInterface::class);
        $pdo
            ->expects($this->once())
            ->method('prepare')
            ->with('SELECT `users`.*, `friendships`.`user_id` AS `__pivot_user_id` FROM `friendships` LEFT OUTER JOIN `users` ON `friendships`.`friend_id` = `users`.`user_id` WHERE (`friendships`.`user_id` IN (?, ?, ?))')
            ->willReturn($stmt);

        $fetcher = $this->getMock(FetcherInterface::class);
        $fetcher
            ->expects($this->once())
            ->method('fetch')
            ->with($this->identicalTo($stmt))
            ->willReturn(new FrozenResultSet($innerElements, null));

        $builder = new SelectBuilder();
        $joinStrategy = new GroupJoin();

        $relation = new ManyToMany(
            'friends',
            'friendships',
            'user_id',
            'user_id',
            'users',
            'friend_id',
            'user_id',
            $pdo,
            $fetcher,
            $builder,
            $joinStrategy
        );

        $result = $relation->join(new FrozenResultSet($outerElements, null));
        $this->assertEquals($expectedResult, iterator_to_array($result));
    }
}
