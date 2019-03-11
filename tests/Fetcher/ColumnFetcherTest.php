<?php

namespace Emonkak\Orm\Tests\Fetcher;

use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\Fetcher\ColumnFetcher;
use Emonkak\Orm\ResultSet\ColumnResultSet;

/**
 * @covers Emonkak\Orm\Fetcher\ColumnFetcher
 */
class ColumnFetcherTest extends \PHPUnit_Framework_TestCase
{
    public function testFetch()
    {
        $fetcher = new ColumnFetcher();

        $stmt = $this->createMock(PDOStatementInterface::class);

        $result = $fetcher->fetch($stmt);
        $this->assertInstanceOf(ColumnResultSet::class, $result);
        $this->assertNull($result->getClass());
    }

    public function testGetClass()
    {
        $fetcher = new ColumnFetcher();
        $this->assertNull($fetcher->getClass());
    }
}
