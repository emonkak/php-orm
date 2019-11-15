<?php

namespace Emonkak\Tests\Orm\Pagination;

use Emonkak\Orm\Pagination\Page;
use Emonkak\Orm\Pagination\EmptyPaginator;
use Emonkak\Orm\ResultSet\EmptyResultSet;

/**
 * @covers Emonkak\Orm\Pagination\EmptyPaginator
 */
class EmptyPaginatorTest extends \PHPUnit_Framework_TestCase
{
    public function testAt()
    {
        $result = (new EmptyPaginator(10))->at(0);
        $this->assertInstanceOf(Page::class, $result);
        $this->assertEmpty(iterator_to_array($result));
    }

    public function testHas()
    {
        $this->assertFalse((new EmptyPaginator(10))->has(0));
    }

    public function testFirstPage()
    {
        $result = (new EmptyPaginator(10))->firstPage();
        $this->assertInstanceOf(Page::class, $result);
        $this->assertEmpty(iterator_to_array($result));
    }

    public function testLastPage()
    {
        $result = (new EmptyPaginator(10))->lastPage();
        $this->assertInstanceOf(Page::class, $result);
        $this->assertEmpty(iterator_to_array($result));
    }

    public function testGetPerPage()
    {
        $this->assertSame(10, (new EmptyPaginator(10))->getPerPage());
    }

    public function testGetItemCount()
    {
        $this->assertSame(0, (new EmptyPaginator(10))->getTotalItems());
    }

    public function testGetPageCount()
    {
        $this->assertSame(0, (new EmptyPaginator(10))->getTotalPages());
    }
}
