<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\Fetcher;

use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\Fetcher\ColumnFetcher;
use Emonkak\Orm\ResultSet\ColumnResultSet;
use PHPUnit\Framework\TestCase;

/**
 * @covers Emonkak\Orm\Fetcher\ColumnFetcher
 */
class ColumnFetcherTest extends TestCase
{
    public function testFetch(): void
    {
        $fetcher = new ColumnFetcher();

        $stmt = $this->createMock(PDOStatementInterface::class);

        $result = $fetcher->fetch($stmt);
        $this->assertInstanceOf(ColumnResultSet::class, $result);
    }

    public function testGetClass(): void
    {
        $fetcher = new ColumnFetcher();
        $this->assertNull($fetcher->getClass());
    }
}
