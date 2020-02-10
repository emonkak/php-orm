<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\Fetcher;

use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\Fetcher\FunctionFetcher;
use Emonkak\Orm\ResultSet\ResultSetInterface;
use Emonkak\Orm\Tests\Fixtures\Model;
use PHPUnit\Framework\TestCase;

/**
 * @covers Emonkak\Orm\Fetcher\FunctionFetcher
 */
class FunctionFetcherTest extends TestCase
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
