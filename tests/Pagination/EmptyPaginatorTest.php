<?php

namespace Emonkak\Tests\Orm\Pagination;

use Emonkak\Orm\Pagination\CountablePage;
use Emonkak\Orm\Pagination\EmptyPaginator;
use Emonkak\Orm\ResultSet\EmptyResultSet;
use Emonkak\Orm\Tests\Fixtures\Model;

/**
 * @covers Emonkak\Orm\Pagination\EmptyPaginator
 */
class EmptyPaginatorTest extends \PHPUnit_Framework_TestCase
{
    public function testAt()
    {
        $result = (new EmptyPaginator(Model::class, 10))->at(0);
        $this->assertInstanceOf(CountablePage::class, $result);
        $this->assertEmpty(iterator_to_array($result));
    }

    public function testHas()
    {
        $this->assertFalse((new EmptyPaginator(Model::class, 10))->has(0));
    }

    public function testFirstPage()
    {
        $result = (new EmptyPaginator(Model::class, 10))->firstPage();
        $this->assertInstanceOf(CountablePage::class, $result);
        $this->assertEmpty(iterator_to_array($result));
    }

    public function testLastPage()
    {
        $result = (new EmptyPaginator(Model::class, 10))->lastPage();
        $this->assertInstanceOf(CountablePage::class, $result);
        $this->assertEmpty(iterator_to_array($result));
    }

    public function testGetPerPage()
    {
        $this->assertSame(10, (new EmptyPaginator(Model::class, 10))->getPerPage());
    }

    public function testGetItemCount()
    {
        $this->assertSame(0, (new EmptyPaginator(Model::class, 10))->getNumItems());
    }

    public function testGetPageCount()
    {
        $this->assertSame(0, (new EmptyPaginator(Model::class, 10))->getNumPages());
    }

    public function testGetClass()
    {
        $this->assertSame(Model::class, (new EmptyPaginator(Model::class, 10))->getClass());
    }
}
