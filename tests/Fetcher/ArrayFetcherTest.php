<?php

namespace Emonkak\Orm\Tests\Fetcher;

use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\Fetcher\ArrayFetcher;
use Emonkak\Orm\ResultSet\ArrayResultSet;

/**
 * @covers Emonkak\Orm\Fetcher\ArrayFetcher
 */
class ArrayFetcherTest extends \PHPUnit_Framework_TestCase
{
    public function testFetch()
    {
        $fetcher = new ArrayFetcher();

        $stmt = $this->getMock(PDOStatementInterface::class);

        $result = $fetcher->fetch($stmt);
        $this->assertInstanceOf(ArrayResultSet::class, $result);
        $this->assertNull($result->getClass());
    }

    public function testGetClass()
    {
        $fetcher = new ArrayFetcher();
        $this->assertNull($fetcher->getClass());
    }
}
