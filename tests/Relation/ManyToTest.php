<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\Relation;

use Emonkak\Database\PDOInterface;
use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\QueryBuilderInterface;
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
        $relationKeyName = 'relation_key';
        $oneToManyTableName = 'one_to_many_table';
        $oneToManyOuterKeyName = 'one_to_many_outer_key';
        $oneToManyInnerKeyName = 'one_to_many_inner_key';
        $manyToOneTableName = 'many_to_one_table';
        $manyToOneOuterKeyName = 'many_to_one_outer_key';
        $manyToOneInnerKeyName = 'many_to_one_inner_key';
        $pivotKeyName = 'pivot_key';

        $queryBuilder = $this->getSelectBuilder();
        $fetcher = $this->createMock(FetcherInterface::class);

        $relation = new ManyTo(
            $relationKeyName,
            $oneToManyTableName,
            $oneToManyOuterKeyName,
            $oneToManyInnerKeyName,
            $manyToOneTableName,
            $manyToOneOuterKeyName,
            $manyToOneInnerKeyName,
            $pivotKeyName,
            $queryBuilder,
            $fetcher
        );

        $this->assertSame($relationKeyName, $relation->getRelationKeyName());
        $this->assertSame($oneToManyTableName, $relation->getOneToManyTableName());
        $this->assertSame($oneToManyOuterKeyName, $relation->getOneToManyOuterKeyName());
        $this->assertSame($oneToManyInnerKeyName, $relation->getOneToManyInnerKeyName());
        $this->assertSame($manyToOneTableName, $relation->getManyToOneTableName());
        $this->assertSame($manyToOneOuterKeyName, $relation->getManyToOneOuterKeyName());
        $this->assertSame($manyToOneInnerKeyName, $relation->getManyToOneInnerKeyName());
        $this->assertSame($pivotKeyName, $relation->getPivotKey());
        $this->assertSame($queryBuilder, $relation->getQueryBuilder());
        $this->assertSame($fetcher, $relation->getFetcher());
    }

    public function testGetResult(): void
    {
        $outerKeys = [1, 2, 3];
        $expectedResult = $this->createMock(ResultSetInterface::class);

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
            ->willReturnCallback(function(QueryBuilderInterface $queryBuilder) use ($pdo, $expectedResult) {
                $queryBuilder->prepare($pdo);
                return $expectedResult;
            });

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
            $fetcher
        );

        $joinStrategy = $this->createMock(JoinStrategyInterface::class);

        $this->assertSame($expectedResult, $relation->getResult($outerKeys, $joinStrategy));
    }
}
