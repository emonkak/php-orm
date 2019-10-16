<?php

namespace Emonkak\Orm\Tests\Pagination;

use Emonkak\Orm\Pagination\CountablePage;
use Emonkak\Orm\Pagination\CountablePaginator;

/**
 * @covers Emonkak\Orm\Pagination\CountablePaginator
 */
class CountablePaginatorTest extends \PHPUnit_Framework_TestCase
{
    public function testAt()
    {
        $perPage = 10;
        $numItems = 21;

        $results = [
            array_fill(0, 10, new \stdClass()),
            array_fill(0, 10, new \stdClass()),
            array_fill(0, 1, new \stdClass())
        ];

        $resultFetcher = $this
            ->getMockBuilder(\stdClass::class)
            ->setMethods(['__invoke'])
            ->getMock();
        $resultFetcher
            ->expects($this->any())
            ->method('__invoke')
            ->will($this->returnValueMap([
                [10, 0, new \ArrayIterator($results[0])],
                [10, 10, new \ArrayIterator($results[1])],
                [10, 20, new \ArrayIterator($results[2])]
            ]));

        $paginator = new CountablePaginator($resultFetcher, $perPage, $numItems);

        $this->assertSame($results[0], iterator_to_array($paginator->firstPage()));
        $this->assertSame($results[2], iterator_to_array($paginator->lastPage()));
        $this->assertSame($results[0], iterator_to_array($paginator->at(0)));
        $this->assertSame($results[1], iterator_to_array($paginator->at(1)));
        $this->assertSame($results[2], iterator_to_array($paginator->at(2)));
        $this->assertEmpty(iterator_to_array($paginator->at(99)));
        $this->assertSame(array_merge(...$results), iterator_to_array($paginator));
    }
}
