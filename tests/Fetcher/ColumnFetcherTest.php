<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\Fetcher;

use Emonkak\Database\PDOInterface;
use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\Fetcher\ColumnFetcher;
use Emonkak\Orm\QueryBuilderInterface;
use Emonkak\Orm\ResultSet\ColumnResultSet;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Emonkak\Orm\Fetcher\ColumnFetcher
 */
class ColumnFetcherTest extends TestCase
{
    public function testConstructor(): void
    {
        $pdo = $this->createMock(PDOInterface::class);
        $columnNumber = 1;

        $fetcher = new ColumnFetcher($pdo, $columnNumber);

        $this->assertSame($pdo, $fetcher->getPDO());
        $this->assertSame($columnNumber, $fetcher->getColumnNumber());
        $this->assertNull($fetcher->getClass());
    }

    public function testFetch(): void
    {
        $pdo = $this->createMock(PDOInterface::class);

        $stmt = $this->createMock(PDOStatementInterface::class);

        $queryBuilder = $this->createMock(QueryBuilderInterface::class);
        $queryBuilder
            ->expects($this->once())
            ->method('prepare')
            ->with($this->identicalTo($pdo))
            ->willReturn($stmt);

        $fetcher = new ColumnFetcher($pdo);

        $result = $fetcher->fetch($queryBuilder);
        $this->assertInstanceOf(ColumnResultSet::class, $result);
    }
}
