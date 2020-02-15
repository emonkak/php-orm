<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\Relation;

use Emonkak\Database\PDOInterface;
use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\Relation\JoinStrategy\JoinStrategyInterface;
use Emonkak\Orm\Relation\OneTo;
use Emonkak\Orm\ResultSet\ResultSetInterface;
use Emonkak\Orm\Tests\QueryBuilderTestTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers Emonkak\Orm\Relation\OneTo
 */
class OneToTest extends TestCase
{
    use QueryBuilderTestTrait;

    public function testConstructor(): void
    {
        $relationKey = 'relation_key';
        $table = 'table';
        $outerKey = 'outer_key';
        $innerKey = 'inner_key';

        $fetcher = $this->createMock(FetcherInterface::class);
        $queryBuilder = $this->getSelectBuilder();
        $unions = [
            'union' => $this->getSelectBuilder()
        ];

        $relationStrategy = new OneTo(
            $relationKey,
            $table,
            $outerKey,
            $innerKey,
            $fetcher,
            $queryBuilder,
            $unions
        );

        $this->assertSame($relationKey, $relationStrategy->getRelationKey());
        $this->assertSame($table, $relationStrategy->getTable());
        $this->assertSame($outerKey, $relationStrategy->getOuterKey());
        $this->assertSame($innerKey, $relationStrategy->getInnerKey());
        $this->assertSame($fetcher, $relationStrategy->getFetcher());
        $this->assertSame($queryBuilder, $relationStrategy->getQueryBuilder());
        $this->assertSame($unions, $relationStrategy->getUnions());
    }

    public function testGetResult(): void
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
            ->with('SELECT * FROM `revisions` WHERE (`revisions`.`revision_id` IN (?, ?, ?))')
            ->willReturn($stmt);

        $fetcher = $this->createMock(FetcherInterface::class);
        $fetcher
            ->expects($this->once())
            ->method('fetch')
            ->will($this->returnCallback(function($queryBuilder) use ($pdo, $expectedResult) {
                $queryBuilder->prepare($pdo);
                return $expectedResult;
            }));

        $queryBuilder = $this->getSelectBuilder();

        $relationStrategy = new OneTo(
            'revisions',
            'revisions',
            'revision_id',
            'revision_id',
            $fetcher,
            $queryBuilder,
            []
        );

        $joinStrategy = $this->createMock(JoinStrategyInterface::class);

        $this->assertSame($expectedResult, $relationStrategy->getResult($outerKeys, $joinStrategy));
    }

    public function testGetResultWithUnion(): void
    {
        $outerKeys = [1, 2, 3];
        $expectedResult = $this->createMock(ResultSetInterface::class);

        $stmt = $this->createMock(PDOStatementInterface::class);
        $stmt
            ->expects($this->exactly(9))
            ->method('bindValue')
            ->withConsecutive(
                [1, 1, \PDO::PARAM_INT],
                [2, 2, \PDO::PARAM_INT],
                [3, 3, \PDO::PARAM_INT],
                [4, 1, \PDO::PARAM_INT],
                [5, 2, \PDO::PARAM_INT],
                [6, 3, \PDO::PARAM_INT],
                [7, 1, \PDO::PARAM_INT],
                [8, 2, \PDO::PARAM_INT],
                [9, 3, \PDO::PARAM_INT]
            )
            ->willReturn(true);

        $pdo = $this->createMock(PDOInterface::class);
        $pdo
            ->expects($this->once())
            ->method('prepare')
            ->with('SELECT * FROM `foo` WHERE (`foo`.`object_id` IN (?, ?, ?)) UNION ALL (SELECT * FROM `bar` WHERE (`bar`.`object_id` IN (?, ?, ?))) UNION ALL (SELECT * FROM `baz` WHERE (`baz`.`object_id` IN (?, ?, ?)))')
            ->willReturn($stmt);

        $fetcher = $this->createMock(FetcherInterface::class);
        $fetcher
            ->expects($this->once())
            ->method('fetch')
            ->will($this->returnCallback(function($query) use ($pdo, $expectedResult) {
                $query->prepare($pdo);
                return $expectedResult;
            }));

        $queryBuilder = $this->getSelectBuilder();

        $relationStrategy = new OneTo(
            'object',
            'foo',
            'object_id',
            'object_id',
            $fetcher,
            $queryBuilder,
            [
                'bar' => $this->getSelectBuilder(),
                'baz' => $this->getSelectBuilder()
            ]
        );

        $joinStrategy = $this->createMock(JoinStrategyInterface::class);

        $this->assertSame($expectedResult, $relationStrategy->getResult($outerKeys, $joinStrategy));
    }
}
