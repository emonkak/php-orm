<?php

namespace Emonkak\Orm\Tests\Pagination;

use Emonkak\Orm\Pagination\PaginatorInterface;
use Emonkak\Orm\Pagination\SequentialPaginator;

/**
 * @covers Emonkak\Orm\Pagination\SequentialPaginator
 */
class SequentialPaginatorTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $perPage = 10;

        $itemsFetcher = $this
            ->getMockBuilder(\stdClass::class)
            ->setMethods(['__invoke'])
            ->getMock();
        $itemsFetcher
            ->expects($this->never())
            ->method('__invoke');

        $paginator = new SequentialPaginator($itemsFetcher, $perPage);

        $this->assertEquals($perPage, $paginator->getPerPage());
    }

    public function testGetIterator()
    {
        $perPage = 10;

        $itemsFetcher = $this
            ->getMockBuilder(\stdClass::class)
            ->setMethods(['__invoke'])
            ->getMock();
        $itemsFetcher
            ->expects($this->exactly(3))
            ->method('__invoke')
            ->will($this->returnCallback(function($limit, $offset) {
                return range($offset, min(29, $offset + $limit - 1));
            }));

        $paginator = new SequentialPaginator($itemsFetcher, $perPage);

        $this->assertEquals(range(0, 29), iterator_to_array($paginator));
    }

    public function testAt()
    {
        $perPage = 10;

        $results = [
            range(0, 10),
            range(10, 20),
            range(20, 29),
        ];

        $itemsFetcher = $this
            ->getMockBuilder(\stdClass::class)
            ->setMethods(['__invoke'])
            ->getMock();
        $itemsFetcher
            ->expects($this->any())
            ->method('__invoke')
            ->will($this->returnValueMap([
                [11, 0, $results[0]],
                [11, 10, $results[1]],
                [11, 20, $results[2]]
            ]));

        $paginator = new SequentialPaginator($itemsFetcher, $perPage);

        $page = $paginator->at(0);
        $this->assertEquals(array_slice($results[0], 0, $perPage), iterator_to_array($page));
        $this->assertSame(0, $page->getIndex());

        $page = $paginator->at(1);
        $this->assertEquals(array_slice($results[1], 0, $perPage), iterator_to_array($page));
        $this->assertSame(1, $page->getIndex());

        $page = $paginator->at(2);
        $this->assertEquals(array_slice($results[2], 0, $perPage), iterator_to_array($page));
        $this->assertSame(2, $page->getIndex());
    }
}
