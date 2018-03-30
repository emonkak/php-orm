<?php

namespace Emonkak\Orm\Tests\Relation;

use Emonkak\Database\PDOInterface;
use Emonkak\Database\PDOStatementInterface;;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\Relation\JoinStrategy\GroupJoin;
use Emonkak\Orm\Relation\JoinStrategy\JoinStrategyInterface;
use Emonkak\Orm\Relation\ManyTo;
use Emonkak\Orm\Relation\OneTo;
use Emonkak\Orm\Relation\Relation;
use Emonkak\Orm\Relation\RelationInterface;
use Emonkak\Orm\Relation\RelationStrategyInterface;
use Emonkak\Orm\ResultSet\EmptyResultSet;
use Emonkak\Orm\ResultSet\PreloadResultSet;
use Emonkak\Orm\Tests\Fixtures\Model;
use Emonkak\Orm\Tests\QueryBuilderTestTrait;

/**
 * @covers Emonkak\Orm\Relation\Relation
 */
class RelationTest extends \PHPUnit_Framework_TestCase
{
    use QueryBuilderTestTrait;

    public function testOneToMany()
    {
        $outerElements = [
            new Model(['user_id' => 1, 'name' => 'foo']),
            new Model(['user_id' => 2, 'name' => 'bar']),
            new Model(['user_id' => 3, 'name' => 'baz']),
        ];
        $innerElements = [
            new Model(['post_id' => 1, 'user_id' => 1, 'content' => 'foo']),
            new Model(['post_id' => 2, 'user_id' => 1, 'content' => 'bar']),
            new Model(['post_id' => 3, 'user_id' => 3, 'content' => 'baz']),
        ];
        $expectedResult = [
            new Model([
                'user_id' => 1,
                'name' => 'foo',
                'posts' => [
                    new Model(['post_id' => 1, 'user_id' => 1, 'content' => 'foo']),
                    new Model(['post_id' => 2, 'user_id' => 1, 'content' => 'bar']),
                ],
            ]),
            new Model([
                'user_id' => 2,
                'name' => 'bar',
                'posts' => [],
            ]),
            new Model([
                'user_id' => 3,
                'name' => 'baz',
                'posts' => [
                    new Model(['post_id' => 3, 'user_id' => 3, 'content' => 'baz']),
                ],
            ]),
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
            ->willReturn(new PreloadResultSet($innerElements, Model::class));

        $builder = $this->createSelectBuilder();

        $relationStrategy = new OneTo(
            'posts',
            'posts',
            'user_id',
            'user_id',
            $pdo,
            $fetcher,
            $builder
        );
        $joinStrategy = new GroupJoin();
        $relation = new Relation($relationStrategy, $joinStrategy);

        $outerResult = new PreloadResultSet($outerElements, Model::class);

        $this->assertSame($relationStrategy, $relation->getRelationStrategy());
        $this->assertSame($joinStrategy, $relation->getJoinStrategy());
        $this->assertEquals($expectedResult, iterator_to_array($relation->associate($outerResult)));
    }

    public function testOneToManyIfEmptyResult()
    {
        $stmt = $this->createMock(PDOStatementInterface::class);
        $pdo = $this->createMock(PDOInterface::class);
        $fetcher = $this->createMock(FetcherInterface::class);
        $builder = $this->createSelectBuilder();

        $relationStrategy = new OneTo(
            'posts',
            'posts',
            'user_id',
            'user_id',
            $pdo,
            $fetcher,
            $builder
        );
        $joinStrategy = new GroupJoin();
        $relation = new Relation($relationStrategy, $joinStrategy);

        $outerResult = new EmptyResultSet(Model::class);

        $this->assertSame($relationStrategy, $relation->getRelationStrategy());
        $this->assertSame($joinStrategy, $relation->getJoinStrategy());
        $this->assertEmpty(iterator_to_array($relation->associate($outerResult)));
    }

    public function testManyTo()
    {
        $outerElements = [
            new Model(['user_id' => 1, 'name' => 'foo']),
            new Model(['user_id' => 2, 'name' => 'bar']),
            new Model(['user_id' => 3, 'name' => 'baz']),
        ];
        $innerElements = [
            new Model(['__pivot_user_id' => 1, 'user_id' => 2, 'name' => 'bar']),
            new Model(['__pivot_user_id' => 1, 'user_id' => 3, 'name' => 'baz']),
            new Model(['__pivot_user_id' => 2, 'user_id' => 1, 'name' => 'foo']),
            new Model(['__pivot_user_id' => 3, 'user_id' => 2, 'name' => 'bar']),
        ];
        $expectedResult = [
            new Model([
                'user_id' => 1,
                'name' => 'foo',
                'friends' => [
                    new Model(['user_id' => 2, 'name' => 'bar']),
                    new Model(['user_id' => 3, 'name' => 'baz']),
                ],
            ]),
            new Model([
                'user_id' => 2,
                'name' => 'bar',
                'friends' => [
                    new Model(['user_id' => 1, 'name' => 'foo']),
                ],
            ]),
            new Model([
                'user_id' => 3,
                'name' => 'baz',
                'friends' => [
                    new Model(['user_id' => 2, 'name' => 'bar']),
                ],
            ]),
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
            ->with('SELECT `users`.*, `friendships`.`user_id` AS `__pivot_user_id` FROM `friendships` LEFT OUTER JOIN `users` ON `friendships`.`friend_id` = `users`.`user_id` WHERE (`friendships`.`user_id` IN (?, ?, ?))')
            ->willReturn($stmt);

        $fetcher = $this->createMock(FetcherInterface::class);
        $fetcher
            ->expects($this->once())
            ->method('fetch')
            ->with($this->identicalTo($stmt))
            ->willReturn(new PreloadResultSet($innerElements, Model::class));

        $builder = $this->createSelectBuilder();

        $relationStrategy = new ManyTo(
            'friends',
            'friendships',
            'user_id',
            'user_id',
            'users',
            'friend_id',
            'user_id',
            $pdo,
            $fetcher,
            $builder
        );
        $joinStrategy = new GroupJoin();
        $relation = new Relation($relationStrategy, $joinStrategy);

        $outerResult = new PreloadResultSet($outerElements, Model::class);

        $this->assertSame($relationStrategy, $relation->getRelationStrategy());
        $this->assertSame($joinStrategy, $relation->getJoinStrategy());
        $this->assertEquals($expectedResult, iterator_to_array($relation->associate($outerResult)));
    }

    public function testWith()
    {
        $childRelation = $this->createMock(RelationInterface::class);

        $relationStrategy1 = $this->createMock(RelationStrategyInterface::class);
        $relationStrategy2 = $this->createMock(RelationStrategyInterface::class);

        $relationStrategy1
            ->expects($this->once())
            ->method('with')
            ->with($this->identicalTo($childRelation))
            ->willReturn($relationStrategy2);

        $joinStrategy = $this->createMock(JoinStrategyInterface::class);

        $relation = new Relation($relationStrategy1, $joinStrategy);

        $newRelation = $relation->with($childRelation);

        $this->assertInstanceOf(Relation::class, $relation);
        $this->assertNotSame($relation, $newRelation);
        $this->assertSame($relationStrategy2, $newRelation->getRelationStrategy());
        $this->assertSame($joinStrategy, $newRelation->getJoinStrategy());
    }
}
