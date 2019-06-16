<?php

namespace Emonkak\Orm\Tests\Relation;

use Emonkak\Database\PDOInterface;
use Emonkak\Database\PDOStatementInterface;;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\Relation\JoinStrategy\GroupJoin;
use Emonkak\Orm\Relation\JoinStrategy\JoinStrategyInterface;
use Emonkak\Orm\Relation\JoinStrategy\OuterJoin;
use Emonkak\Orm\Relation\ManyTo;
use Emonkak\Orm\Relation\OneTo;
use Emonkak\Orm\Relation\Relation;
use Emonkak\Orm\Relation\RelationInterface;
use Emonkak\Orm\Relation\RelationStrategyInterface;
use Emonkak\Orm\ResultSet\EmptyResultSet;
use Emonkak\Orm\ResultSet\PreloadedResultSet;
use Emonkak\Orm\Tests\Fixtures\Model;
use Emonkak\Orm\Tests\QueryBuilderTestTrait;

/**
 * @covers Emonkak\Orm\Relation\Relation
 */
class RelationTest extends \PHPUnit_Framework_TestCase
{
    use QueryBuilderTestTrait;

    public function testOneToOne()
    {
        $outerElements = [
            new Model(['post_id' => 1, 'user_id' => 1]),
            new Model(['post_id' => 2, 'user_id' => 1]),
            new Model(['post_id' => 3, 'user_id' => 3]),
            new Model(['post_id' => 4, 'user_id' => null]),
        ];
        $innerElements = [
            new Model(['user_id' => 1]),
            new Model(['user_id' => 2]),
            new Model(['user_id' => 3]),
        ];
        $expectedResult = [
            new Model([
                'post_id' => 1,
                'user_id' => 1,
                'user' => new Model(['user_id' => 1]),
            ]),
            new Model([
                'post_id' => 2,
                'user_id' => 1,
                'user' => new Model(['user_id' => 1]),
            ]),
            new Model([
                'post_id' => 3,
                'user_id' => 3,
                'user' => new Model(['user_id' => 3]),
            ]),
            new Model([
                'post_id' => 4,
                'user_id' => null
            ]),
        ];

        $outerResult = new PreloadedResultSet($outerElements, Model::class);
        $innerResult = new PreloadedResultSet($innerElements, Model::class);

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
            ->with('SELECT * FROM `users` WHERE (`users`.`user_id` IN (?, ?))')
            ->willReturn($stmt);

        $fetcher = $this->createMock(FetcherInterface::class);
        $fetcher
            ->expects($this->once())
            ->method('fetch')
            ->with($this->identicalTo($stmt))
            ->willReturn($innerResult);

        $childRelation = $this->createMock(RelationInterface::class);
        $childRelation
            ->expects($this->once())
            ->method('associate')
            ->with($this->identicalTo($innerResult))
            ->will($this->returnArgument(0));

        $builder = $this->getSelectBuilder();

        $relationStrategy = new OneTo(
            'user',
            'users',
            'user_id',
            'user_id',
            $pdo,
            $fetcher,
            $builder,
            []
        );
        $joinStrategy = new OuterJoin();
        $relation = new Relation($relationStrategy, $joinStrategy, [$childRelation]);

        $this->assertSame($relationStrategy, $relation->getRelationStrategy());
        $this->assertSame($joinStrategy, $relation->getJoinStrategy());
        $this->assertEquals($expectedResult, iterator_to_array($relation->associate($outerResult)));
    }

    public function testOneToMany()
    {
        $outerElements = [
            new Model(['user_id' => 1]),
            new Model(['user_id' => 2]),
            new Model(['user_id' => 3]),
            new Model(['user_id' => null]),
        ];
        $innerElements = [
            new Model(['post_id' => 1, 'user_id' => 1]),
            new Model(['post_id' => 2, 'user_id' => 1]),
            new Model(['post_id' => 3, 'user_id' => 3]),
        ];
        $expectedResult = [
            new Model([
                'user_id' => 1,
                'posts' => [
                    new Model(['post_id' => 1, 'user_id' => 1]),
                    new Model(['post_id' => 2, 'user_id' => 1]),
                ],
            ]),
            new Model([
                'user_id' => 2,
                'posts' => [],
            ]),
            new Model([
                'user_id' => 3,
                'posts' => [
                    new Model(['post_id' => 3, 'user_id' => 3]),
                ],
            ]),
            new Model([
                'user_id' => null,
                'posts' => [
                ],
            ]),
        ];

        $outerResult = new PreloadedResultSet($outerElements, Model::class);
        $innerResult = new PreloadedResultSet($innerElements, Model::class);

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
            ->willReturn($innerResult);

        $childRelation = $this->createMock(RelationInterface::class);
        $childRelation
            ->expects($this->once())
            ->method('associate')
            ->with($this->identicalTo($innerResult))
            ->will($this->returnArgument(0));

        $builder = $this->getSelectBuilder();

        $relationStrategy = new OneTo(
            'posts',
            'posts',
            'user_id',
            'user_id',
            $pdo,
            $fetcher,
            $builder,
            []
        );
        $joinStrategy = new GroupJoin();
        $relation = new Relation($relationStrategy, $joinStrategy, [$childRelation]);

        $this->assertSame($relationStrategy, $relation->getRelationStrategy());
        $this->assertSame($joinStrategy, $relation->getJoinStrategy());
        $this->assertEquals($expectedResult, iterator_to_array($relation->associate($outerResult)));
    }

    public function testOneToManyIfResultIsEmpty()
    {
        $stmt = $this->createMock(PDOStatementInterface::class);
        $pdo = $this->createMock(PDOInterface::class);
        $fetcher = $this->createMock(FetcherInterface::class);
        $builder = $this->getSelectBuilder();

        $relationStrategy = new OneTo(
            'posts',
            'posts',
            'user_id',
            'user_id',
            $pdo,
            $fetcher,
            $builder,
            []
        );
        $joinStrategy = new GroupJoin();
        $relation = new Relation($relationStrategy, $joinStrategy);

        $outerResult = new EmptyResultSet(Model::class);

        $this->assertSame($relationStrategy, $relation->getRelationStrategy());
        $this->assertSame($joinStrategy, $relation->getJoinStrategy());
        $this->assertEmpty(iterator_to_array($relation->associate($outerResult)));
    }

    public function testOneToManyIfOuterKeysIsEmpty()
    {
        $outerElements = [new Model(['job_id' => null])];
        $outerResult = new PreloadedResultSet($outerElements, Model::class);

        $stmt = $this->createMock(PDOStatementInterface::class);
        $pdo = $this->createMock(PDOInterface::class);
        $fetcher = $this->createMock(FetcherInterface::class);
        $builder = $this->getSelectBuilder();

        $relationStrategy = new OneTo(
            'jobs',
            'jobs',
            'job_id',
            'job_id',
            $pdo,
            $fetcher,
            $builder,
            []
        );
        $joinStrategy = new GroupJoin();
        $relation = new Relation($relationStrategy, $joinStrategy);

        $this->assertSame($relationStrategy, $relation->getRelationStrategy());
        $this->assertSame($joinStrategy, $relation->getJoinStrategy());
        $this->assertSame($outerElements, iterator_to_array($relation->associate($outerResult)));
    }

    public function testManyTo()
    {
        $outerElements = [
            new Model(['user_id' => 1]),
            new Model(['user_id' => 2]),
            new Model(['user_id' => 3]),
        ];
        $innerElements = [
            new Model(['__pivot_user_id' => 1, 'user_id' => 2]),
            new Model(['__pivot_user_id' => 1, 'user_id' => 3]),
            new Model(['__pivot_user_id' => 2, 'user_id' => 1]),
            new Model(['__pivot_user_id' => 3, 'user_id' => 2]),
        ];
        $expectedResult = [
            new Model([
                'user_id' => 1,
                'friends' => [
                    new Model(['user_id' => 2]),
                    new Model(['user_id' => 3]),
                ],
            ]),
            new Model([
                'user_id' => 2,
                'friends' => [
                    new Model(['user_id' => 1]),
                ],
            ]),
            new Model([
                'user_id' => 3,
                'friends' => [
                    new Model(['user_id' => 2]),
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
            ->with('SELECT `users`.*, `friendships`.`user_id` AS `__pivot_user_id` FROM `users` LEFT OUTER JOIN `friendships` ON `users`.`user_id` = `friendships`.`friend_id` WHERE (`friendships`.`user_id` IN (?, ?, ?))')
            ->willReturn($stmt);

        $fetcher = $this->createMock(FetcherInterface::class);
        $fetcher
            ->expects($this->once())
            ->method('fetch')
            ->with($this->identicalTo($stmt))
            ->willReturn(new PreloadedResultSet($innerElements, Model::class));

        $builder = $this->getSelectBuilder();

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
            $builder,
            []
        );
        $joinStrategy = new GroupJoin();
        $relation = new Relation($relationStrategy, $joinStrategy);

        $outerResult = new PreloadedResultSet($outerElements, Model::class);

        $this->assertSame($relationStrategy, $relation->getRelationStrategy());
        $this->assertSame($joinStrategy, $relation->getJoinStrategy());
        $this->assertEquals($expectedResult, iterator_to_array($relation->associate($outerResult)));
    }

    public function testWith()
    {
        $relationStrategy = $this->createMock(RelationStrategyInterface::class);
        $joinStrategy = $this->createMock(JoinStrategyInterface::class);
        $childRelation = $this->createMock(RelationInterface::class);

        $relation = new Relation($relationStrategy, $joinStrategy);
        $newRelation = $relation->with($childRelation);

        $this->assertNotSame($relation, $newRelation);
        $this->assertSame([$childRelation], $newRelation->getChildRelations());
    }
}
