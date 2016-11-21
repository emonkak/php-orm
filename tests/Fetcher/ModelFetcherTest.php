<?php

namespace Emonkak\Orm\Tests\Fetcher;

use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\Fetcher\ModelFetcher;
use Emonkak\Orm\ResultSet\ResultSetInterface;

/**
 * @covers Emonkak\Orm\Fetcher\ModelFetcher
 */
class ModelFetcherTest extends \PHPUnit_Framework_TestCase
{
    public function testFetch()
    {
        $fetcher = new ModelFetcher('stdClass');

        $stmt = $this->getMock(PDOStatementInterface::class);

        $result = $fetcher->fetch($stmt);

        $this->assertInstanceOf(ResultSetInterface::class, $result);
        $this->assertSame('stdClass', $result->getClass());
    }

    public function testGetClass()
    {
        $fetcher = new ModelFetcher('stdClass');
        $this->assertSame('stdClass', $fetcher->getClass());
    }
}
