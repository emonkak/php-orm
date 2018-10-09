<?php

namespace Emonkak\Orm\Tests\ResultSet;

use Emonkak\Orm\Pagination\Paginator;
use Emonkak\Orm\ResultSet\PaginatedResultSet;
use Emonkak\Orm\ResultSet\ResultSetInterface;

/**
 * @covers Emonkak\Orm\ResultSet\PaginatedResultSet
 */
class PaginatedResultSetTest extends \PHPUnit_Framework_TestCase
{
    public function testGetClass()
    {
        $innerResult = $this->createMock(ResultSetInterface::class);
        $innerResult
            ->expects($this->once())
            ->method('getClass')
            ->willReturn(\stdClass::class);

        $paginator = $this->createMock(Paginator::class);

        $result = new PaginatedResultSet($innerResult, $paginator, 123);

        $this->assertSame(\stdClass::class, $result->getClass());
    }

    public function testGetIterator()
    {
        $innerResult = $this->createMock(ResultSetInterface::class);

        $paginator = $this->createMock(Paginator::class);

        $result = new PaginatedResultSet($innerResult, $paginator, 123);

        $this->assertSame($innerResult, $result->getIterator());
    }

    public function testGetIndex()
    {
        $innerResult = $this->createMock(ResultSetInterface::class);

        $paginator = $this->createMock(Paginator::class);

        $result = new PaginatedResultSet($innerResult, $paginator, 123);

        $this->assertSame(123, $result->getIndex());
    }

    public function testGetInitialItemIndex()
    {
        $innerResult = $this->createMock(ResultSetInterface::class);

        $paginator = $this->createMock(Paginator::class);
        $paginator
            ->expects($this->any())
            ->method('getPerPage')
            ->willReturn(10);

        $result = new PaginatedResultSet($innerResult, $paginator, 0);
        $this->assertSame(0, $result->getInitialItemIndex());

        $result = new PaginatedResultSet($innerResult, $paginator, 1);
        $this->assertSame(10, $result->getInitialItemIndex());

        $result = new PaginatedResultSet($innerResult, $paginator, 2);
        $this->assertSame(20, $result->getInitialItemIndex());
    }

    public function testGetPageNum()
    {
        $innerResult = $this->createMock(ResultSetInterface::class);

        $paginator = $this->createMock(Paginator::class);

        $result = new PaginatedResultSet($innerResult, $paginator, 123);

        $this->assertSame(123, $result->getIndex());
    }

    public function testGetPaginator()
    {
        $innerResult = $this->createMock(ResultSetInterface::class);

        $paginator = $this->createMock(Paginator::class);

        $result = new PaginatedResultSet($innerResult, $paginator, 123);

        $this->assertSame($paginator, $result->getPaginator());
    }

    public function testNextPage()
    {
        $innerResult = $this->createMock(ResultSetInterface::class);

        $paginator = $this->createMock(Paginator::class);
        $paginator
            ->expects($this->any())
            ->method('getPageCount')
            ->willReturn(1000);
        $paginator
            ->expects($this->once())
            ->method('at')
            ->with(124)
            ->willReturn($expected = new PaginatedResultSet($innerResult, $paginator, 124));

        $result = new PaginatedResultSet($innerResult, $paginator, 123);

        $this->assertTrue($result->hasNextPage());
        $this->assertSame($expected, $result->nextPage());
    }

    /**
     * @expectedException OutOfRangeException
     */
    public function testNextPageThrowsOutOfRangeException()
    {
        $innerResult = $this->createMock(ResultSetInterface::class);

        $paginator = $this->createMock(Paginator::class);
        $paginator
            ->expects($this->any())
            ->method('getPageCount')
            ->willReturn(1000);

        $result = new PaginatedResultSet($innerResult, $paginator, 1000);

        $this->assertFalse($result->hasNextPage());
        $result->nextPage();
    }

    public function testPrevPage()
    {
        $innerResult = $this->createMock(ResultSetInterface::class);

        $paginator = $this->createMock(Paginator::class);
        $paginator
            ->expects($this->any())
            ->method('getPageCount')
            ->willReturn(1000);
        $paginator
            ->expects($this->once())
            ->method('at')
            ->with(122)
            ->willReturn($expected = new PaginatedResultSet($innerResult, $paginator, 122));

        $result = new PaginatedResultSet($innerResult, $paginator, 123);

        $this->assertTrue($result->hasPrevPage());
        $this->assertSame($expected, $result->prevPage());
    }

    /**
     * @expectedException OutOfRangeException
     */
    public function testPrevPageThrowsOutOfRangeException()
    {
        $innerResult = $this->createMock(ResultSetInterface::class);

        $paginator = $this->createMock(Paginator::class);
        $paginator
            ->expects($this->any())
            ->method('getPageCount')
            ->willReturn(1000);

        $result = new PaginatedResultSet($innerResult, $paginator, 0);

        $this->assertFalse($result->hasPrevPage());
        $result->prevPage();
    }

    public function testIsFirstPage()
    {
        $innerResult = $this->createMock(ResultSetInterface::class);

        $paginator = $this->createMock(Paginator::class);
        $paginator
            ->expects($this->any())
            ->method('getPageCount')
            ->willReturn(1000);

        $result = new PaginatedResultSet($innerResult, $paginator, 0);
        $this->assertTrue($result->isFirstPage());

        $result = new PaginatedResultSet($innerResult, $paginator, 123);
        $this->assertFalse($result->isFirstPage());

        $result = new PaginatedResultSet($innerResult, $paginator, 999);
        $this->assertFalse($result->isFirstPage());
    }

    public function testIsLastPage()
    {
        $innerResult = $this->createMock(ResultSetInterface::class);

        $paginator = $this->createMock(Paginator::class);
        $paginator
            ->expects($this->any())
            ->method('getPageCount')
            ->willReturn(1000);

        $result = new PaginatedResultSet($innerResult, $paginator, 0);
        $this->assertFalse($result->isLastPage());

        $result = new PaginatedResultSet($innerResult, $paginator, 123);
        $this->assertFalse($result->isLastPage());

        $result = new PaginatedResultSet($innerResult, $paginator, 999);
        $this->assertTrue($result->isLastPage());
    }
}
