<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\Relation;

use Emonkak\Database\PDOInterface;
use Emonkak\Database\PDOStatementInterface;
use Emonkak\Enumerable\LooseEqualityComparer;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\QueryBuilderInterface;
use Emonkak\Orm\Relation\JoinStrategy\GroupJoin;
use Emonkak\Orm\Relation\JoinStrategy\OuterJoin;
use Emonkak\Orm\Relation\ManyTo;
use Emonkak\Orm\Relation\OneTo;
use Emonkak\Orm\Relation\Relation;
use Emonkak\Orm\ResultSet\PreloadedResultSet;
use Emonkak\Orm\ResultSet\ResultSetInterface;
use Emonkak\Orm\Tests\Fixtures\Model;
use Emonkak\Orm\Tests\QueryBuilderTestTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Emonkak\Orm\Relation\Relation
 */
class RelationTest extends TestCase
{
    use QueryBuilderTestTrait;

    public function testOneToOne(): void
    {
        $outerClass = Model::class;
        // Test whether IDs of different types can be joined.
        $outerResult = [
            new Model(['post_id' => 1, 'user_id' => 1]),
            new Model(['post_id' => 2, 'user_id' => 1]),
            new Model(['post_id' => 3, 'user_id' => 3]),
            new Model(['post_id' => 4, 'user_id' => null]),
        ];
        $innerResult = [
            new Model(['user_id' => '1']),
            new Model(['user_id' => '2']),
            new Model(['user_id' => '3']),
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
                'user_id' => null,
                'user' => null,
            ]),
        ];

        $stmt = $this->createMock(PDOStatementInterface::class);
        $stmt
            ->expects($this->exactly(2))
            ->method('bindValue')
            ->willReturnMap([
                [1, 1, \PDO::PARAM_INT, true],
                [2, 3, \PDO::PARAM_INT, true],
            ]);

        $pdo = $this->createMock(PDOInterface::class);
        $pdo
            ->expects($this->once())
            ->method('prepare')
            ->with('SELECT * FROM `users` WHERE (`users`.`user_id` IN (?, ?))')
            ->willReturn($stmt);

        $queryBuilder = $this->getSelectBuilder();

        $fetcher = $this->createMock(FetcherInterface::class);
        $fetcher
            ->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(function(QueryBuilderInterface $queryBuilder) use ($pdo, $innerResult): ResultSetInterface {
                $queryBuilder->prepare($pdo);
                return new PreloadedResultSet($innerResult);
            });

        /** @var LooseEqualityComparer<mixed> */
        $comparer = LooseEqualityComparer::getInstance();
        /** @var OneTo<Model,mixed> */
        $relationStrategy = new OneTo(
            'user',
            'users',
            'user_id',
            'user_id',
            $queryBuilder,
            $fetcher
        );
        /** @var OuterJoin<Model,Model,mixed,Model> */
        $joinStrategy = new OuterJoin(
            function(Model $outer): mixed {
                return $outer->user_id;
            },
            function(Model $inner): mixed {
                return $inner->user_id;
            },
            function(Model $outer, ?Model $inner): Model {
                $outer->user = $inner;
                return $outer;
            },
            $comparer
        );
        $relation = new Relation($outerClass, $relationStrategy, $joinStrategy);

        $this->assertSame($outerClass, $relation->getResultClass());
        $this->assertSame($relationStrategy, $relation->getRelationStrategy());
        $this->assertSame($joinStrategy, $relation->getJoinStrategy());
        $this->assertEquals($expectedResult, iterator_to_array($relation->associate($outerResult, $outerClass)));
    }

    public function testOneToMany(): void
    {
        $outerClass = Model::class;
        $outerResult = [
            new Model(['user_id' => 1]),
            new Model(['user_id' => 2]),
            new Model(['user_id' => 3]),
            new Model(['user_id' => null]),
        ];
        $innerResult = [
            new Model(['post_id' => 1, 'user_id' => '1']),
            new Model(['post_id' => 2, 'user_id' => '1']),
            new Model(['post_id' => 3, 'user_id' => '3']),
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

        $stmt = $this->createMock(PDOStatementInterface::class);
        $stmt
            ->expects($this->exactly(3))
            ->method('bindValue')
            ->willReturnMap([
                [1, 1, \PDO::PARAM_INT, true],
                [2, 2, \PDO::PARAM_INT, true],
                [3, 3, \PDO::PARAM_INT, true],
            ]);

        $pdo = $this->createMock(PDOInterface::class);
        $pdo
            ->expects($this->once())
            ->method('prepare')
            ->with('SELECT * FROM `posts` WHERE (`posts`.`user_id` IN (?, ?, ?))')
            ->willReturn($stmt);

        $queryBuilder = $this->getSelectBuilder();

        $fetcher = $this->createMock(FetcherInterface::class);
        $fetcher
            ->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(function(QueryBuilderInterface $queryBuilder) use ($pdo, $innerResult): ResultSetInterface {
                $queryBuilder->prepare($pdo);
                return new PreloadedResultSet($innerResult);
            });

        /** @var LooseEqualityComparer<mixed> */
        $comparer = LooseEqualityComparer::getInstance();
        /** @var OneTo<Model,mixed> */
        $relationStrategy = new OneTo(
            'posts',
            'posts',
            'user_id',
            'user_id',
            $queryBuilder,
            $fetcher
        );
        /** @var GroupJoin<Model,Model,mixed,Model> */
        $joinStrategy = new GroupJoin(
            function(Model $outer): mixed {
                return $outer->user_id;
            },
            function(Model $inner): mixed {
                return $inner->user_id;
            },
            function(Model $outer, array $inner): Model {
                $outer->posts = $inner;
                return $outer;
            },
            $comparer
        );
        $relation = new Relation($outerClass, $relationStrategy, $joinStrategy);

        $this->assertSame($outerClass, $relation->getResultClass());
        $this->assertSame($relationStrategy, $relation->getRelationStrategy());
        $this->assertSame($joinStrategy, $relation->getJoinStrategy());
        $this->assertEquals($expectedResult, iterator_to_array($relation->associate($outerResult, $outerClass)));
    }

    public function testOneToManyIfResultIsEmpty(): void
    {
        $outerClass = Model::class;

        $stmt = $this->createMock(PDOStatementInterface::class);
        $pdo = $this->createMock(PDOInterface::class);
        $queryBuilder = $this->getSelectBuilder();
        $fetcher = $this->createMock(FetcherInterface::class);

        /** @var LooseEqualityComparer<mixed> */
        $comparer = LooseEqualityComparer::getInstance();
        /** @var OneTo<Model,mixed> */
        $relationStrategy = new OneTo(
            'posts',
            'posts',
            'user_id',
            'user_id',
            $queryBuilder,
            $fetcher
        );
        /** @var GroupJoin<Model,Model,mixed,Model> */
        $joinStrategy = new GroupJoin(
            function(Model $outer): mixed {
                return $outer->user_id;
            },
            function(Model $inner): mixed {
                return $inner->user_id;
            },
            function(Model $outer, array $inner): Model {
                $outer->posts = $inner;
                return $outer;
            },
            $comparer
        );
        $relation = new Relation($outerClass, $relationStrategy, $joinStrategy);

        $outerResult = [];

        $this->assertSame($relationStrategy, $relation->getRelationStrategy());
        $this->assertSame($joinStrategy, $relation->getJoinStrategy());
        $this->assertEmpty(iterator_to_array($relation->associate($outerResult, $outerClass)));
    }

    public function testOneToManyIfOuterKeysIsEmpty(): void
    {
        $outerClass = Model::class;
        $outerElements = [new Model(['user_id' => null])];

        $stmt = $this->createMock(PDOStatementInterface::class);
        $queryBuilder = $this->getSelectBuilder();
        $fetcher = $this->createMock(FetcherInterface::class);

        /** @var LooseEqualityComparer<mixed> */
        $comparer = LooseEqualityComparer::getInstance();
        /** @var OneTo<Model,mixed> */
        $relationStrategy = new OneTo(
            'posts',
            'posts',
            'user_id',
            'user_id',
            $queryBuilder,
            $fetcher
        );
        /** @var GroupJoin<Model,Model,mixed,Model> */
        $joinStrategy = new GroupJoin(
            function(Model $outer): mixed {
                return $outer->user_id;
            },
            function(Model $inner): mixed {
                return $inner->user_id;
            },
            function(Model $outer, array $inner): Model {
                $outer->posts = $inner;
                return $outer;
            },
            $comparer
        );
        $relation = new Relation($outerClass, $relationStrategy, $joinStrategy);

        $this->assertSame($relationStrategy, $relation->getRelationStrategy());
        $this->assertSame($joinStrategy, $relation->getJoinStrategy());
        $this->assertEquals([
            new Model(['user_id' => null, 'posts' => []]),
        ], iterator_to_array($relation->associate($outerElements, $outerClass)));
    }

    public function testManyTo(): void
    {
        $outerClass = Model::class;
        $outerResult = [
            new Model(['user_id' => 1]),
            new Model(['user_id' => 2]),
            new Model(['user_id' => 3]),
        ];
        $innerResult = [
            new Model(['user_id' => 2, '__pivot_key' => 1]),
            new Model(['user_id' => 3, '__pivot_key' => 1]),
            new Model(['user_id' => 1, '__pivot_key' => 2]),
            new Model(['user_id' => 2, '__pivot_key' => 3]),
        ];
        $expectedResult = [
            new Model([
                'user_id' => 1,
                'friends' => [
                    new Model(['user_id' => 2, '__pivot_key' => 1]),
                    new Model(['user_id' => 3, '__pivot_key' => 1]),
                ],
            ]),
            new Model([
                'user_id' => 2,
                'friends' => [
                    new Model(['user_id' => 1, '__pivot_key' => 2]),
                ],
            ]),
            new Model([
                'user_id' => 3,
                'friends' => [
                    new Model(['user_id' => 2, '__pivot_key' => 3]),
                ],
            ]),
        ];

        $stmt = $this->createMock(PDOStatementInterface::class);
        $stmt
            ->expects($this->exactly(3))
            ->method('bindValue')
            ->willReturnMap([
                [1, 1, \PDO::PARAM_INT, true],
                [2, 2, \PDO::PARAM_INT, true],
                [3, 3, \PDO::PARAM_INT, true],
            ]);

        $pdo = $this->createMock(PDOInterface::class);
        $pdo
            ->expects($this->once())
            ->method('prepare')
            ->with('SELECT `users`.*, `friendships`.`user_id` AS `__pivot_key` FROM `users` LEFT OUTER JOIN `friendships` ON `users`.`user_id` = `friendships`.`friend_id` WHERE (`friendships`.`user_id` IN (?, ?, ?))')
            ->willReturn($stmt);

        $queryBuilder = $this->getSelectBuilder();

        $fetcher = $this->createMock(FetcherInterface::class);
        $fetcher
            ->expects($this->once())
            ->method('fetch')
            ->willReturnCallback(function(QueryBuilderInterface $queryBuilder) use ($pdo, $innerResult): ResultSetInterface {
                $queryBuilder->prepare($pdo);
                return new PreloadedResultSet($innerResult);
            });

        /** @var LooseEqualityComparer<mixed> */
        $comparer = LooseEqualityComparer::getInstance();
        /** @var ManyTo<Model,mixed> */
        $relationStrategy = new ManyTo(
            'friends',
            'friendships',
            'user_id',
            'user_id',
            'users',
            'friend_id',
            'user_id',
            '__pivot_key',
            $queryBuilder,
            $fetcher
        );
        /** @var OuterJoin<Model,Model,mixed,Model> */
        $joinStrategy = new GroupJoin(
            function(Model $outer): mixed {
                return $outer->user_id;
            },
            function(Model $inner): mixed {
                return $inner->__pivot_key;
            },
            function(Model $outer, array $inner): Model {
                $outer->friends = $inner;
                return $outer;
            },
            $comparer
        );
        $relation = new Relation($outerClass, $relationStrategy, $joinStrategy);

        $this->assertSame($relationStrategy, $relation->getRelationStrategy());
        $this->assertSame($joinStrategy, $relation->getJoinStrategy());
        $this->assertEquals($expectedResult, iterator_to_array($relation->associate($outerResult, $outerClass)));
    }
}
