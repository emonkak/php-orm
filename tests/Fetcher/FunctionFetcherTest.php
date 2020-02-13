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
    public function testFetch(): void
    {
        $fetcher = FunctionFetcher::ofConstructor(Model::class);

        $stmt = $this->createMock(PDOStatementInterface::class);
        $stmt
            ->expects($this->once())
            ->method('fetchAll')
            ->willReturn([['foo' => 'bar']]);

        $result = $fetcher->fetch($stmt);

        $this->assertInstanceOf(ResultSetInterface::class, $result);
        $this->assertEquals([new Model(['foo' => 'bar'])], $result->toArray());
    }

    public function testGetClass(): void
    {
        $fetcher = FunctionFetcher::ofConstructor(Model::class);
        $this->assertSame(Model::class, $fetcher->getClass());
    }
}
