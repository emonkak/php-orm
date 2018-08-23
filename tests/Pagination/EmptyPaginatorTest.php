<?php

namespace Emonkak\Tests\Orm\Pagination;

use Emonkak\Orm\Pagination\EmptyPaginator;
use Emonkak\Orm\ResultSet\EmptyResultSet;
use Emonkak\Orm\ResultSet\PaginatedResultSet;
use Emonkak\Orm\Tests\Fixtures\Model;

/**
 * @covers Emonkak\Orm\Pagination\EmptyPaginator
 */
class EmptyPaginatorTest extends \PHPUnit_Framework_TestCase
{
    public function testAt()
    {
        $result = (new EmptyPaginator(Model::class, 10))->at(0);
        $this->assertInstanceOf(PaginatedResultSet::class, $result);
        $this->assertEmpty(iterator_to_array($result));
    }

    /**
     * @expectedException OutOfRangeException
     */
    public function testAtThrowsOutOfRangeException()
    {
        (new EmptyPaginator(Model::class, 10))->at(-1);
    }

    public function testFirstPage()
    {
        $result = (new EmptyPaginator(Model::class, 10))->firstPage();
        $this->assertInstanceOf(PaginatedResultSet::class, $result);
        $this->assertEmpty(iterator_to_array($result));
    }

    public function testLastPage()
    {
        $result = (new EmptyPaginator(Model::class, 10))->lastPage();
        $this->assertInstanceOf(PaginatedResultSet::class, $result);
        $this->assertEmpty(iterator_to_array($result));
    }

    public function testGetPerPage()
    {
        $this->assertSame(10, (new EmptyPaginator(Model::class, 10))->getPerPage());
    }

    public function testGetItemCount()
    {
        $this->assertSame(0, (new EmptyPaginator(Model::class, 10))->getItemCount());
    }

    public function testGetPageCount()
    {
        $this->assertSame(0, (new EmptyPaginator(Model::class, 10))->getPageCount());
    }

    public function testGetClass()
    {
        $this->assertSame(Model::class, (new EmptyPaginator(Model::class, 10))->getClass());
    }
}
