<?php

namespace Emonkak\Orm\Tests\Fetcher;

use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\Fetcher\ClassFetcher;
use Emonkak\Orm\ResultSet\ClassResultSet;

/**
 * @covers Emonkak\Orm\Fetcher\ClassFetcher
 */
class ClassFetcherTest extends \PHPUnit_Framework_TestCase
{
    public function testFetch()
    {
        $fetcher = new ClassFetcher('stdClass');

        $stmt = $this->createMock(PDOStatementInterface::class);

        $result = $fetcher->fetch($stmt);

        $this->assertInstanceOf(ClassResultSet::class, $result);
        $this->assertSame('stdClass', $result->getClass());
    }

    public function testGetClass()
    {
        $fetcher = new ClassFetcher('stdClass');
        $this->assertSame('stdClass', $fetcher->getClass());
    }
}
