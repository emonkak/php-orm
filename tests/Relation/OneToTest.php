<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\Relation;

use Emonkak\Database\PDOInterface;
use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\QueryBuilderInterface;
use Emonkak\Orm\Relation\JoinStrategy\JoinStrategyInterface;
use Emonkak\Orm\Relation\OneTo;
use Emonkak\Orm\ResultSet\ResultSetInterface;
use Emonkak\Orm\Tests\QueryBuilderTestTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Emonkak\Orm\Relation\OneTo
 */
class OneToTest extends TestCase
{
    use QueryBuilderTestTrait;

    public function testConstructor(): void
    {
        $relationKeyName = 'relation_key';
        $tableName = 'table';
        $outerKeyName = 'outer_key';
        $innerKeyName = 'inner_key';

        $queryBuilder = $this->getSelectBuilder();
        $fetcher = $this->createMock(FetcherInterface::class);

        $relationStrategy = new OneTo(
            $relationKeyName,
            $tableName,
            $outerKeyName,
            $innerKeyName,
            $queryBuilder,
            $fetcher
        );

        $this->assertSame($relationKeyName, $relationStrategy->getRelationKeyName());
        $this->assertSame($tableName, $relationStrategy->getTableName());
        $this->assertSame($outerKeyName, $relationStrategy->getOuterKeyName());
        $this->assertSame($innerKeyName, $relationStrategy->getInnerKeyName());
        $this->assertSame($queryBuilder, $relationStrategy->getQueryBuilder());
        $this->assertSame($fetcher, $relationStrategy->getFetcher());
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
            ->with('SELECT * FROM `revisions` WHERE (`revisions`.`revision_id` IN (?, ?, ?))')
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

        $relationStrategy = new OneTo(
            'revisions',
            'revisions',
            'revision_id',
            'revision_id',
            $queryBuilder,
            $fetcher
        );

        $joinStrategy = $this->createMock(JoinStrategyInterface::class);

        $this->assertSame($expectedResult, $relationStrategy->getResult($outerKeys, $joinStrategy));
    }
}
