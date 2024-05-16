<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\Fetchable;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\QueryBuilderInterface;
use Emonkak\Orm\ResultSet\ResultSetInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Emonkak\Orm\Fetchable
 */
class FetchableTest extends TestCase
{
    public function testGetResult(): void
    {
        $result = $this->createMock(ResultSetInterface::class);

        $pdo = $this->createMock(PDOInterface::class);

        $fetchable = $this->getMockBuilder(FetchableMock::class)
            ->onlyMethods(['build', 'prepare'])
            ->getMock();

        $fetcher = $this->createMock(FetcherInterface::class);
        $fetcher
            ->expects($this->once())
            ->method('fetch')
            ->with($this->identicalTo($fetchable))
            ->willReturn($result);

        $this->assertSame($result, $fetchable->getResult($fetcher));
    }
}

abstract class FetchableMock implements QueryBuilderInterface
{
    use Fetchable;
}
