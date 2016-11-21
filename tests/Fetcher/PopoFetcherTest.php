<?php

namespace Emonkak\Orm\Tests\Fetcher;

use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\Fetcher\PopoFetcher;
use Emonkak\Orm\ResultSet\PopoResultSet;

/**
 * @covers Emonkak\Orm\Fetcher\PopoFetcher
 */
class PopoFetcherTest extends \PHPUnit_Framework_TestCase
{
    public function testFetch()
    {
        $fetcher = new PopoFetcher('stdClass');

        $stmt = $this->getMock(PDOStatementInterface::class);

        $result = $fetcher->fetch($stmt);

        $this->assertInstanceOf(PopoResultSet::class, $result);
        $this->assertSame('stdClass', $result->getClass());
    }

    public function testGetClass()
    {
        $fetcher = new PopoFetcher('stdClass');
        $this->assertSame('stdClass', $fetcher->getClass());
    }
}
