<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\Relation;

use Emonkak\Database\PDOInterface;
use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\Relation\JoinStrategy\JoinStrategyInterface;
use Emonkak\Orm\Relation\ManyTo;
use Emonkak\Orm\ResultSet\ResultSetInterface;
use Emonkak\Orm\Tests\QueryBuilderTestTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Emonkak\Orm\Relation\ManyTo
 */
class ManyToTest extends TestCase
{
    use QueryBuilderTestTrait;

    public function testConstructor(): void
    {
        $relationKey = 'relation_key';
        $oneToManyTable = 'one_to_many_table';
        $oneToManyOuterKey = 'one_to_many_outer_key';
        $oneToManyInnerKey = 'one_to_many_inner_key';
        $manyToOneTable = 'many_to_one_table';
        $manyToOneOuterKey = 'many_to_one_outer_key';
        $manyToOneInnerKey = 'many_to_one_inner_key';
        $pivotKey = 'pivot_key';

        $queryBuilder = $this->getSelectBuilder();
        $fetcher = $this->createMock(FetcherInterface::class);

        $relation = new ManyTo(
            $relationKey,
            $oneToManyTable,
            $oneToManyOuterKey,
            $oneToManyInnerKey,
            $manyToOneTable,
            $manyToOneOuterKey,
            $manyToOneInnerKey,
            $pivotKey,
            $queryBuilder,
            $fetcher
        );

        $this->assertSame($relationKey, $relation->getRelationKey());
        $this->assertSame($oneToManyTable, $relation->getOneToManyTable());
        $this->assertSame($oneToManyOuterKey, $relation->getOneToManyOuterKey());
        $this->assertSame($oneToManyInnerKey, $relation->getOneToManyInnerKey());
        $this->assertSame($manyToOneTable, $relation->getManyToOneTable());
        $this->assertSame($manyToOneOuterKey, $relation->getManyToOneOuterKey());
        $this->assertSame($manyToOneInnerKey, $relation->getManyToOneInnerKey());
        $this->assertSame($pivotKey, $relation->getPivotKey());
        $this->assertSame($queryBuilder, $relation->getQueryBuilder());
        $this->assertSame($fetcher, $relation->getFetcher());
    }

    public function testGetResult(): void
    {
        $outerKeys = [1, 2, 3];
        $expectedResult = $this->createMock(ResultSetInterface::class);
        $expectedBindValues = [
            [1, 1, \PDO::PARAM_INT],
            [2, 2, \PDO::PARAM_INT],
            [3, 3, \PDO::PARAM_INT],
        ];

        $stmt = $this->createMock(PDOStatementInterface::class);
        $stmt
            ->expects($this->exactly(3))
            ->method('bindValue')
            ->willReturnCallback(function(...$args) use (&$expectedBindValues) {
                $this->assertSame(array_shift($expectedBindValues), $args);
                return true;
            });

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
            ->will($this->returnCallback(function($queryBuilder) use ($pdo, $expectedResult) {
                $queryBuilder->prepare($pdo);
                return $expectedResult;
            }));

        $relation = new ManyTo(
            'friends',
            'friendships',
            'user_id',
            'user_id',
            'users',
            'friend_id',
            'user_id',
            '__pivot_key',
            $queryBuilder,
            $fetcher,
            []
        );

        $joinStrategy = $this->createMock(JoinStrategyInterface::class);

        $this->assertSame($expectedResult, $relation->getResult($outerKeys, $joinStrategy));
    }
}
