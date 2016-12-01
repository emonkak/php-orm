<?php

namespace Emonkak\Orm\Tests\Fetcher;

use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\Fetcher\RelationFetcher;
use Emonkak\Orm\Relation\RelationInterface;
use Emonkak\Orm\ResultSet\RelationResultSet;
use Emonkak\Orm\ResultSet\ResultSetInterface;

/**
 * @covers Emonkak\Orm\Fetcher\RelationFetcher
 */
class RelationFetcherTest extends \PHPUnit_Framework_TestCase
{
    public function testFetch()
    {
        $mockedResultSet = $this->createMock(ResultSetInterface::class);
        $mockedResultSet
            ->expects($this->once())
            ->method('getClass')
            ->willReturn('stdClass');

        $mockedFetcher = $this->createMock(FetcherInterface::class);
        $mockedFetcher
            ->expects($this->once())
            ->method('fetch')
            ->willReturn($mockedResultSet);

        $relation = $this->createMock(RelationInterface::class);

        $fetcher = new RelationFetcher($mockedFetcher, $relation);

        $stmt = $this->createMock(PDOStatementInterface::class);

        $result = $fetcher->fetch($stmt);

        $this->assertInstanceOf(RelationResultSet::class, $result);
        $this->assertSame('stdClass', $result->getClass());
    }

    public function testGetClass()
    {
        $mockedFetcher = $this->createMock(FetcherInterface::class);
        $mockedFetcher
            ->expects($this->once())
            ->method('getClass')
            ->willReturn('stdClass');

        $relation = $this->createMock(RelationInterface::class);

        $fetcher = new RelationFetcher($mockedFetcher, $relation);

        $this->assertSame('stdClass', $fetcher->getClass());
    }
}