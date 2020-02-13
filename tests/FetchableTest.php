<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests;

use Emonkak\Database\PDOInterface;
use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\Fetchable;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\Relation\RelationInterface;
use Emonkak\Orm\ResultSet\ResultSetInterface;
use Emonkak\Orm\Sql;
use Emonkak\Orm\Tests\Fixtures\IterableResultSetInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers Emonkak\Orm\Fetchable
 */
class FetchableTest extends TestCase
{
    public function testGetResult(): void
    {
        $result = $this->createMock(ResultSetInterface::class);

        $pdo = $this->createMock(PDOInterface::class);

        $stmt = $this->createMock(PDOStatementInterface::class);

        $fetchable = $this->getMockForTrait(Fetchable::class);
        $fetchable
            ->expects($this->once())
            ->method('prepare')
            ->with($this->identicalTo($pdo))
            ->willReturn($stmt);

        $fetcher = $this->createMock(FetcherInterface::class);
        $fetcher
            ->expects($this->once())
            ->method('fetch')
            ->with($this->identicalTo($stmt))
            ->willReturn($result);

        $this->assertSame($result, $fetchable->getResult($pdo, $fetcher));
    }
}
