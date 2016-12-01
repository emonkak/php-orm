<?php

namespace Emonkak\Orm\Tests\Pagination;

use Emonkak\Orm\Pagination\PaginatorInterface;
use Emonkak\Orm\Pagination\PaginatorIterator;
use Emonkak\Orm\ResultSet\PaginatedResultSet;

/**
 * @covers Emonkak\Orm\Pagination\PaginatorIterator
 */
class PaginatorIteratorTest extends \PHPUnit_Framework_TestCase
{
    public function testGetIterator()
    {
        $results = [
            $this->createMock(PaginatedResultSet::class),
            $this->createMock(PaginatedResultSet::class),
            $this->createMock(PaginatedResultSet::class),
        ];

        $paginator = $this->createMock(PaginatorInterface::class);
        $paginator
            ->expects($this->once())
            ->method('getNumPages')
            ->willReturn(3);
        $paginator
            ->expects($this->exactly(3))
            ->method('at')
            ->will($this->onConsecutiveCalls(...$results));

        $iterator = new PaginatorIterator($paginator);

        $this->assertSame($results, iterator_to_array($iterator));
    }
}
