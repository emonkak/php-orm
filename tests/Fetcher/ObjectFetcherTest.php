<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\Fetcher;

use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\Fetcher\ObjectFetcher;
use Emonkak\Orm\ResultSet\ObjectResultSet;
use PHPUnit\Framework\TestCase;

/**
 * @covers Emonkak\Orm\Fetcher\ObjectFetcher
 */
class ObjectFetcherTest extends TestCase
{
    public function testFetch(): void
    {
        $fetcher = new ObjectFetcher(\stdClass::class, ['foo']);

        $stmt = $this->createMock(PDOStatementInterface::class);

        $result = $fetcher->fetch($stmt);

        $this->assertInstanceOf(ObjectResultSet::class, $result);
        $this->assertSame(\stdClass::class, $result->getClass());
        $this->assertEquals(['foo'], $result->getConstructorArguments());
    }

    public function testGetClass(): void
    {
        $fetcher = new ObjectFetcher(\stdClass::class);
        $this->assertSame(\stdClass::class, $fetcher->getClass());
    }
}
