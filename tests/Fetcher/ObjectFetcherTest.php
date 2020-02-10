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
    public function testFetch()
    {
        $fetcher = new ObjectFetcher('stdClass', ['foo']);

        $stmt = $this->createMock(PDOStatementInterface::class);

        $result = $fetcher->fetch($stmt);

        $this->assertInstanceOf(ObjectResultSet::class, $result);
        $this->assertSame('stdClass', $result->getClass());
    }

    public function testGetClass()
    {
        $fetcher = new ObjectFetcher('stdClass');
        $this->assertSame('stdClass', $fetcher->getClass());
    }
}
