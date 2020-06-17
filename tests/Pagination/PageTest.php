<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\Pagination;

use Emonkak\Orm\Pagination\Page;
use Emonkak\Orm\Pagination\PaginatorInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Emonkak\Orm\Pagination\Page
 */
class PageTest extends TestCase
{
    public function testConstructor(): void
    {
        $items = new \EmptyIterator();
        $perPage = 10;
        $index = 1;

        $paginator = $this->createMock(PaginatorInterface::class);
        $paginator
            ->expects($this->once())
            ->method('getPerPage')
            ->willReturn($perPage);

        $page = new Page($items, $index, $paginator);

        $this->assertSame($items, $page->getIterator());
        $this->assertSame($perPage, $page->getPerPage());
        $this->assertSame($index, $page->getIndex());
        $this->assertSame($paginator, $page->getPaginator());
    }

    public function testNext(): void
    {
        $items = new \EmptyIterator();
        $index = 1;

        $paginator = $this->createMock(PaginatorInterface::class);
        $paginator
            ->expects($this->once())
            ->method('at')
            ->with($index + 1)
            ->willReturn($nextPage = new Page(new \EmptyIterator(), $index + 1, $paginator));

        $page = new Page($items, $index, $paginator);

        $this->assertSame($nextPage, $page->next());
    }

    public function testPrevious(): void
    {
        $items = new \EmptyIterator();
        $index = 1;

        $paginator = $this->createMock(PaginatorInterface::class);
        $paginator
            ->expects($this->once())
            ->method('at')
            ->with($index - 1)
            ->willReturn($previousPage = new Page(new \EmptyIterator(), $index - 1, $paginator));

        $page = new Page($items, $index, $paginator);

        $this->assertSame($previousPage, $page->previous());
    }

    public function testHasPrevious(): void
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

    public function testHasNext(): void
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
}
