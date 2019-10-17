<?php

namespace Emonkak\Orm\Tests\Pagination;

use Emonkak\Enumerable\EnumerableInterface;
use Emonkak\Orm\Pagination\Page;
use Emonkak\Orm\Pagination\PaginatorInterface;
use Emonkak\Orm\ResultSet\ResultSetInterface;

/**
 * @covers Emonkak\Orm\Pagination\Page
 */
class PageTest extends \PHPUnit_Framework_TestCase
{
    public function testGetIterator()
    {
        $elements = $this->createMock(EnumerableInterface::class);

        $paginator = $this->createMock(PaginatorInterface::class);

        $result = new Page($elements, 123, $paginator);

        $this->assertSame($elements, $result->getIterator());
    }

    public function testGetIndex()
    {
        $elements = $this->createMock(EnumerableInterface::class);

        $paginator = $this->createMock(PaginatorInterface::class);

        $result = new Page($elements, 123, $paginator);

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

        $result = new Page($elements, 0, $paginator);
        $this->assertSame(0, $result->getOffset());

        $result = new Page($elements, 1, $paginator);
        $this->assertSame(10, $result->getOffset());

        $result = new Page($elements, 2, $paginator);
        $this->assertSame(20, $result->getOffset());
    }

    public function testGetPageNum()
    {
        $elements = $this->createMock(EnumerableInterface::class);

        $paginator = $this->createMock(PaginatorInterface::class);

        $result = new Page($elements, 123, $paginator);

        $this->assertSame(123, $result->getIndex());
    }

    public function testGetPaginator()
    {
        $elements = $this->createMock(EnumerableInterface::class);

        $paginator = $this->createMock(PaginatorInterface::class);

        $result = new Page($elements, 123, $paginator);

        $this->assertSame($paginator, $result->getPaginator());
    }

    public function testNext()
    {
        $elements = $this->createMock(EnumerableInterface::class);

        $paginator = $this->createMock(PaginatorInterface::class);
        $paginator
            ->expects($this->once())
            ->method('at')
            ->with(124)
            ->willReturn($expected = new Page($elements, 124, $paginator));

        $result = new Page($elements, 123, $paginator);

        $this->assertSame($expected, $result->next());
    }

    public function testPrevious()
    {
        $elements = $this->createMock(EnumerableInterface::class);

        $paginator = $this->createMock(PaginatorInterface::class);
        $paginator
            ->expects($this->once())
            ->method('at')
            ->with(122)
            ->willReturn($expected = new Page($elements, 122, $paginator));

        $result = new Page($elements, 123, $paginator);

        $this->assertSame($expected, $result->previous());
    }

    public function testHasNext()
    {
        $elements = $this->createMock(EnumerableInterface::class);

        $paginator = $this->createMock(PaginatorInterface::class);
        $paginator
            ->expects($this->once())
            ->method('has')
            ->with(124)
            ->willReturn(true);

        $result = new Page($elements, 123, $paginator);

        $this->assertTrue($result->hasNext());
    }

    public function testHasPrevious()
    {
        $elements = $this->createMock(EnumerableInterface::class);

        $paginator = $this->createMock(PaginatorInterface::class);
        $paginator
            ->expects($this->once())
            ->method('has')
            ->with(122)
            ->willReturn(true);

        $result = new Page($elements, 123, $paginator);

        $this->assertTrue($result->hasPrevious());
    }

    public function testIsFirst()
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

        $result = new Page($elements, 0, $paginator);
        $this->assertTrue($result->isFirst());

        $result = new Page($elements, 123, $paginator);
        $this->assertFalse($result->isFirst());

        $result = new Page($elements, 999, $paginator);
        $this->assertFalse($result->isFirst());
    }

    public function testIsLast()
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

        $result = new Page($elements, 0, $paginator);
        $this->assertFalse($result->isLast());

        $result = new Page($elements, 123, $paginator);
        $this->assertFalse($result->isLast());

        $result = new Page($elements, 999, $paginator);
        $this->assertTrue($result->isLast());
    }
}
