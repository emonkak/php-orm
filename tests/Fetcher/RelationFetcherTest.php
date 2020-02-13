<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\Fetcher;

use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\Fetcher\RelationFetcher;
use Emonkak\Orm\Relation\RelationInterface;
use Emonkak\Orm\ResultSet\RelationResultSet;
use Emonkak\Orm\ResultSet\ResultSetInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers Emonkak\Orm\Fetcher\RelationFetcher
 */
class RelationFetcherTest extends TestCase
{
    public function testConstructor(): void
    {
        $resultClass = \stdClass::class;

        $fetcher = $this->createMock(FetcherInterface::class);

        $relation = $this->createMock(RelationInterface::class);
        $relation
            ->expects($this->once())
            ->method('getResultClass')
            ->willReturn($resultClass);

        $relationFetcher = new RelationFetcher($fetcher, $relation);

        $this->assertSame($fetcher, $relationFetcher->getFetcher());
        $this->assertSame($relation, $relationFetcher->getRelation());
        $this->assertSame($resultClass, $relationFetcher->getClass());
    }

    public function testFetch(): void
    {
        $outerClass = \stdClass::class;

        $outerResult = $this->createMock(ResultSetInterface::class);

        $fetcher = $this->createMock(FetcherInterface::class);
        $fetcher
            ->expects($this->once())
            ->method('fetch')
            ->willReturn($outerResult);
        $fetcher
            ->expects($this->once())
            ->method('getClass')
            ->willReturn($outerClass);

        $relation = $this->createMock(RelationInterface::class);

        $stmt = $this->createMock(PDOStatementInterface::class);

        $relationFetcher = new RelationFetcher($fetcher, $relation);

        $relationResult = $relationFetcher->fetch($stmt);

        $this->assertInstanceOf(RelationResultSet::class, $relationResult);
        $this->assertSame($outerResult, $relationResult->getOuterResult());
        $this->assertSame($outerClass, $relationResult->getOuterClass());
    }
}
