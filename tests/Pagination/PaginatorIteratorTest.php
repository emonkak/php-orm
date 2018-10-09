<?php

namespace Emonkak\Orm\Tests\Pagination;

use Emonkak\Orm\Pagination\Paginator;
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
        $results[0]
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([['foo' => 123]]));
        $results[1]
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([['bar' => 456]]));
        $results[2]
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([['baz' => 789]]));

        $paginator = $this->createMock(Paginator::class);
        $paginator
            ->expects($this->once())
            ->method('getPageCount')
            ->willReturn(3);
        $paginator
            ->expects($this->exactly(3))
            ->method('at')
            ->will($this->onConsecutiveCalls(...$results));

        $iterator = new PaginatorIterator($paginator);

        $this->assertEquals([['foo' => 123], ['bar' => 456], ['baz' => 789]], iterator_to_array($iterator));
    }
}
