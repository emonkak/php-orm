<?php

namespace Emonkak\Orm\Tests\ResultSet;

use Emonkak\Enumerable\EnumerableInterface;
use Emonkak\Orm\Pagination\PaginatorInterface;
use Emonkak\Orm\ResultSet\PaginatedResultSet;
use Emonkak\Orm\ResultSet\ResultSetInterface;

/**
 * @covers Emonkak\Orm\ResultSet\PaginatedResultSet
 */
class PaginatedResultSetTest extends \PHPUnit_Framework_TestCase
{
    public function testGetClass()
    {
        $elements = $this->createMock(EnumerableInterface::class);

        $paginator = $this->createMock(PaginatorInterface::class);
        $paginator
            ->expects($this->once())
            ->method('getClass')
            ->willReturn(\stdClass::class);

        $result = new PaginatedResultSet($elements, $paginator, 123);

        $this->assertSame(\stdClass::class, $result->getClass());
    }

    public function testGetIterator()
    {
        $elements = $this->createMock(EnumerableInterface::class);

        $paginator = $this->createMock(PaginatorInterface::class);

        $result = new PaginatedResultSet($elements, $paginator, 123);

        $this->assertSame($elements, $result->getIterator());
    }

    public function testGetIndex()
    {
        $elements = $this->createMock(EnumerableInterface::class);

        $paginator = $this->createMock(PaginatorInterface::class);

        $result = new PaginatedResultSet($elements, $paginator, 123);

        $this->assertSame(123, $result->getIndex());
    }

    public function testGetOffset()
    {
        $elements = $this->createMock(EnumerableInterface::class);

        $paginator = $this->createMock(PaginatorInterface::class);
        $paginator
            ->expects($this->any())
            ->method('getPerPage')
            ->willReturn(10);

        $result = new PaginatedResultSet($elements, $paginator, 0);
        $this->assertSame(0, $result->getOffset());

        $result = new PaginatedResultSet($elements, $paginator, 1);
        $this->assertSame(10, $result->getOffset());

        $result = new PaginatedResultSet($elements, $paginator, 2);
        $this->assertSame(20, $result->getOffset());
    }

    public function testGetPageNum()
    {
        $elements = $this->createMock(EnumerableInterface::class);

        $paginator = $this->createMock(PaginatorInterface::class);

        $result = new PaginatedResultSet($elements, $paginator, 123);

        $this->assertSame(123, $result->getIndex());
    }

    public function testGetPaginator()
    {
        $elements = $this->createMock(EnumerableInterface::class);

        $paginator = $this->createMock(PaginatorInterface::class);

        $result = new PaginatedResultSet($elements, $paginator, 123);

        $this->assertSame($paginator, $result->getPaginator());
    }

    public function testNextPage()
    {
        $elements = $this->createMock(EnumerableInterface::class);

        $paginator = $this->createMock(PaginatorInterface::class);
        $paginator
            ->expects($this->once())
            ->method('at')
            ->with(124)
            ->willReturn($expected = new PaginatedResultSet($elements, $paginator, 124));

        $result = new PaginatedResultSet($elements, $paginator, 123);

        $this->assertSame($expected, $result->nextPage());
    }

    public function testPrevPage()
    {
        $elements = $this->createMock(EnumerableInterface::class);

        $paginator = $this->createMock(PaginatorInterface::class);
        $paginator
            ->expects($this->once())
            ->method('at')
            ->with(122)
            ->willReturn($expected = new PaginatedResultSet($elements, $paginator, 122));

        $result = new PaginatedResultSet($elements, $paginator, 123);

        $this->assertSame($expected, $result->prevPage());
    }

    public function testHasNextPage()
    {
        $elements = $this->createMock(EnumerableInterface::class);

        $paginator = $this->createMock(PaginatorInterface::class);
        $paginator
            ->expects($this->once())
            ->method('has')
            ->with(124)
            ->willReturn(true);

        $result = new PaginatedResultSet($elements, $paginator, 123);

        $this->assertTrue($result->hasNextPage());
    }

    public function testHasPrevPage()
    {
        $elements = $this->createMock(EnumerableInterface::class);

        $paginator = $this->createMock(PaginatorInterface::class);
        $paginator
            ->expects($this->once())
            ->method('has')
            ->with(122)
            ->willReturn(true);

        $result = new PaginatedResultSet($elements, $paginator, 123);

        $this->assertTrue($result->hasPrevPage());
    }

    public function testIsFirstPage()
    {
        $elements = $this->createMock(EnumerableInterface::class);

        $paginator = $this->createMock(PaginatorInterface::class);
        $paginator
            ->method('has')
            ->will($this->returnValueMap([
                [-1, false],
                [122, true],
                [998, true]
            ]));

        $result = new PaginatedResultSet($elements, $paginator, 0);
        $this->assertTrue($result->isFirstPage());

        $result = new PaginatedResultSet($elements, $paginator, 123);
        $this->assertFalse($result->isFirstPage());

        $result = new PaginatedResultSet($elements, $paginator, 999);
        $this->assertFalse($result->isFirstPage());
    }

    public function testIsLastPage()
    {
        $elements = $this->createMock(EnumerableInterface::class);

        $paginator = $this->createMock(PaginatorInterface::class);
        $paginator
            ->method('has')
            ->will($this->returnValueMap([
                [1, true],
                [124, true],
                [1000, false]
            ]));

        $result = new PaginatedResultSet($elements, $paginator, 0);
        $this->assertFalse($result->isLastPage());

        $result = new PaginatedResultSet($elements, $paginator, 123);
        $this->assertFalse($result->isLastPage());

        $result = new PaginatedResultSet($elements, $paginator, 999);
        $this->assertTrue($result->isLastPage());
    }
}
