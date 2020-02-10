<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\Pagination;

use Emonkak\Orm\Pagination\Page;
use Emonkak\Orm\Pagination\PaginatorInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers Emonkak\Orm\Pagination\Page
 */
class PageTest extends TestCase
{
    public function testConstructor()
    {
        $items = new \EmptyIterator();
        $index = 1;

        $paginator = $this->createMock(PaginatorInterface::class);

        $page = new Page($items, $index, $paginator);

        $this->assertSame($items, $page->getIterator());
        $this->assertSame($index, $page->getIndex());
        $this->assertSame($paginator, $page->getPaginator());
    }

    /**
     * @dataProvider prividerGetOffset
     */
    public function testGetOffset($index, $perPage, $expectedOffset)
    {
        $items = new \EmptyIterator();
        $totalPages = 10;
        $perPage = 10;

        $paginator = $this->createMock(PaginatorInterface::class);
        $paginator
            ->expects($this->once())
            ->method('getPerPage')
            ->willReturn($perPage);

        $page = new Page($items, $index, $paginator);

        $this->assertSame($expectedOffset, $page->getOffset());
    }

    public function prividerGetOffset()
    {
        return [
            [0, 10, 0],
            [1, 10, 10],
            [2, 10, 20]
        ];
    }

    public function testNext()
    {
        $items = new \EmptyIterator();
        $index = 1;

        $nextPage = $this->createMock(MockedPage::class);

        $paginator = $this->createMock(PaginatorInterface::class);
        $paginator
            ->expects($this->once())
            ->method('at')
            ->with($index + 1)
            ->willReturn($nextPage);

        $page = new Page($items, $index, $paginator);

        $this->assertSame($nextPage, $page->next());
    }

    public function testPrevious()
    {
        $items = new \EmptyIterator();
        $index = 1;

        $previousPage = $this->createMock(MockedPage::class);

        $paginator = $this->createMock(PaginatorInterface::class);
        $paginator
            ->expects($this->once())
            ->method('at')
            ->with($index - 1)
            ->willReturn($previousPage);

        $page = new Page($items, $index, $paginator);

        $this->assertSame($previousPage, $page->previous());
    }

    public function testHasPrevious()
    {
        $items = new \EmptyIterator();
        $index = 1;

        $paginator = $this->createMock(PaginatorInterface::class);
        $paginator
            ->expects($this->once())
            ->method('has')
            ->with($index - 1)
            ->willReturn(true);

        $page = new Page($items, $index, $paginator);

        $this->assertTrue($page->hasPrevious());
    }

    public function testHasNext()
    {
        $items = new \EmptyIterator();
        $index = 1;

        $paginator = $this->createMock(PaginatorInterface::class);
        $paginator
            ->expects($this->once())
            ->method('has')
            ->with($index + 1)
            ->willReturn(true);

        $page = new Page($items, $index, $paginator);

        $this->assertTrue($page->hasNext());
    }

    public function testIsFirst()
    {
        $items = new \EmptyIterator();
        $index = 0;

        $paginator = $this->createMock(PaginatorInterface::class);
        $paginator
            ->expects($this->once())
            ->method('has')
            ->with($index - 1)
            ->willReturn(false);

        $page = new Page($items, $index, $paginator);

        $this->assertTrue($page->isFirst());
    }

    public function testIsLast()
    {
        $items = new \EmptyIterator();
        $index = 1;

        $paginator = $this->createMock(PaginatorInterface::class);
        $paginator
            ->expects($this->once())
            ->method('has')
            ->with($index + 1)
            ->willReturn(false);

        $page = new Page($items, $index, $paginator);

        $this->assertTrue($page->isLast());
    }
}
