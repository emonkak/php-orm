<?php

namespace Emonkak\Orm\Tests\Fetcher;

use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\Fetcher\FunctionFetcher;
use Emonkak\Orm\ResultSet\ResultSetInterface;
use Emonkak\Orm\Tests\Fixtures\Model;

/**
 * @covers Emonkak\Orm\Fetcher\FunctionFetcher
 */
class FunctionFetcherTest extends \PHPUnit_Framework_TestCase
{
    public function testFetch()
    {
        $fetcher = FunctionFetcher::ofConstructor(Model::class);

        $stmt = $this->createMock(PDOStatementInterface::class);
        $stmt
            ->expects($this->once())
            ->method('fetch')
            ->willReturn([]);

        $result = $fetcher->fetch($stmt);

        $this->assertInstanceOf(ResultSetInterface::class, $result);
        $this->assertSame(Model::class, $result->getClass());
        $this->assertInstanceOf(Model::class, $result->first());
    }

    public function testGetClass()
    {
        $fetcher = FunctionFetcher::ofConstructor(Model::class);
        $this->assertSame(Model::class, $fetcher->getClass());
    }
}
