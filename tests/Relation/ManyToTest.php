<?php

namespace Emonkak\Orm\Tests\Relation;

use Emonkak\Database\PDOInterface;
use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\Relation\ManyTo;
use Emonkak\Orm\Relation\RelationInterface;
use Emonkak\Orm\ResultSet\ResultSetInterface;
use Emonkak\Orm\Tests\Fixtures\Model;
use Emonkak\Orm\Tests\QueryBuilderTestTrait;

/**
 * @covers Emonkak\Orm\Relation\ManyTo
 */
class ManyToTest extends \PHPUnit_Framework_TestCase
{
    use QueryBuilderTestTrait;

    public function testConstructor()
    {
        $relationKey = 'relation_key';
        $oneToManyTable = 'one_to_many_table';
        $oneToManyOuterKey = 'one_to_many_outer_key';
        $oneToManyInnerKey = 'one_to_many_inner_key';
        $manyToOneTable = 'many_to_one_table';
        $manyToOneOuterKey = 'many_to_one_outer_key';
        $manyToOneInnerKey = 'many_to_one_inner_key';

        $pdo = $this->createMock(PDOInterface::class);
        $fetcher = $this->createMock(FetcherInterface::class);
        $builder = $this->getSelectBuilder();

        $relation = new ManyTo(
            $relationKey,
            $oneToManyTable,
            $oneToManyOuterKey,
            $oneToManyInnerKey,
            $manyToOneTable,
            $manyToOneOuterKey,
            $manyToOneInnerKey,
            $pdo,
            $fetcher,
            $builder
        );

        $this->assertSame($relationKey, $relation->getRelationKey());
        $this->assertSame($oneToManyTable, $relation->getOneToManyTable());
        $this->assertSame($oneToManyOuterKey, $relation->getOneToManyOuterKey());
        $this->assertSame($oneToManyInnerKey, $relation->getOneToManyInnerKey());
        $this->assertSame($manyToOneTable, $relation->getManyToOneTable());
        $this->assertSame($manyToOneOuterKey, $relation->getManyToOneOuterKey());
        $this->assertSame($manyToOneInnerKey, $relation->getManyToOneInnerKey());
        $this->assertSame($pdo, $relation->getPdo());
        $this->assertSame($fetcher, $relation->getFetcher());
        $this->assertSame($builder, $relation->getBuilder());
    }

    public function testGetResult()
    {
        $outerKeys = [1, 2, 3];
        $expectedResult = $this->createMock(ResultSetInterface::class);

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
            ->willReturn($expectedResult);

        $builder = $this->getSelectBuilder();

        $relation = new ManyTo(
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

        $this->assertSame($expectedResult, $relation->getResult($outerKeys));
    }

    public function testSelectorResolvings()
    {
        $relationStrategy = new ManyTo(
            'friends',
            'friendships',
            'user_id',
            'user_id',
            'users',
            'friend_id',
            'user_id',
            $this->createMock(PDOInterface::class),
            $this->createMock(FetcherInterface::class),
            $this->getSelectBuilder()
        );

        $outerKeySelector = $relationStrategy->getOuterKeySelector(Model::class);
        $innerKeySelector = $relationStrategy->getInnerKeySelector(Model::class);
        $resultSelector = $relationStrategy->getResultSelector(Model::class, Model::class);

        $outer = new Model(['user_id' => 123]);
        $inner = [
            new Model(['__pivot_user_id' => 123, 'user_id' => 456]),
            new Model(['__pivot_user_id' => 123, 'user_id' => 789]),
        ];

        $expectedResult = new Model([
            'user_id' => 123,
            'friends' => [
                new Model(['user_id' => 456]),
                new Model(['user_id' => 789]),
            ],
        ]);

        $this->assertSame(123, $outerKeySelector($outer));
        $this->assertSame(123, $innerKeySelector($inner[0]));
        $this->assertSame(123, $innerKeySelector($inner[1]));
        $this->assertEquals($expectedResult, $resultSelector($outer, $inner));
    }
}
