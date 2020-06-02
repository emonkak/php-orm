<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\Fetcher;

use Emonkak\Database\PDOInterface;
use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\Fetcher\ArrayFetcher;
use Emonkak\Orm\QueryBuilderInterface;
use Emonkak\Orm\ResultSet\ArrayResultSet;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Emonkak\Orm\Fetcher\ArrayFetcher
 */
class ArrayFetcherTest extends TestCase
{
    public function testGetClass(): void
    {
        $pdo = $this->createMock(PDOInterface::class);

        $fetcher = new ArrayFetcher($pdo);

        $this->assertSame($pdo, $fetcher->getPdo());
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

        $fetcher = new ArrayFetcher($pdo);

        $result = $fetcher->fetch($queryBuilder);
        $this->assertInstanceOf(ArrayResultSet::class, $result);
    }
}
