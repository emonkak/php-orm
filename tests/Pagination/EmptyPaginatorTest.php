<?php

declare(strict_types=1);

namespace Emonkak\Tests\Orm\Pagination;

use Emonkak\Orm\Pagination\Page;
use Emonkak\Orm\Pagination\EmptyPaginator;
use PHPUnit\Framework\TestCase;

/**
 * @covers Emonkak\Orm\Pagination\EmptyPaginator
 */
class EmptyPaginatorTest extends TestCase
{
    public function testAt(): void
    {
        $result = (new EmptyPaginator(10))->at(0);
        $this->assertInstanceOf(Page::class, $result);
        $this->assertEmpty(iterator_to_array($result));
    }

    public function testHas(): void
    {
        $this->assertFalse((new EmptyPaginator(10))->has(0));
    }

    public function testFirstPage(): void
    {
        $result = (new EmptyPaginator(10))->firstPage();
        $this->assertInstanceOf(Page::class, $result);
        $this->assertEmpty(iterator_to_array($result));
    }

    public function testLastPage(): void
    {
        $result = (new EmptyPaginator(10))->lastPage();
        $this->assertInstanceOf(Page::class, $result);
        $this->assertEmpty(iterator_to_array($result));
    }

    public function testGetPerPage(): void
    {
        $this->assertSame(10, (new EmptyPaginator(10))->getPerPage());
    }

    public function testGetItemCount(): void
    {
        $this->assertSame(0, (new EmptyPaginator(10))->getTotalItems());
    }

    public function testGetPageCount(): void
    {
        $this->assertSame(0, (new EmptyPaginator(10))->getTotalPages());
    }
}
