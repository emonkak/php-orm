<?php

namespace Emonkak\Orm\Tests\Pagination;

use Emonkak\Orm\Pagination\SequentialPageIterator;

/**
 * @covers Emonkak\Orm\Pagination\SequentialPageIterator
 */
class SequentialPageIteratorTest extends \PHPUnit_Framework_TestCase
{
    public function testFrom()
    {
        $index = 1;
        $perPage = 10;
        $items = range(10, 20);
        $expectedItems = range(10, 19);

        $itemsFetcher = $this
            ->getMockBuilder(\stdClass::class)
            ->setMethods(['__invoke'])
            ->getMock();
        $itemsFetcher
            ->expects($this->once())
            ->method('__invoke')
            ->will($this->returnValueMap([
                [10, 11, $items]
            ]));

        $iterator = SequentialPageIterator::from($index, $perPage, $itemsFetcher);

        $this->assertSame($index, $iterator->getIndex());
        $this->assertSame($perPage, $iterator->getPerPage());
        $this->assertSame(10, $iterator->getOffset());
        $this->assertEquals($expectedItems, $iterator->getItems());
        $this->assertEquals($expectedItems, iterator_to_array($iterator));
    }

    public function testIterate()
    {
        $index = 0;
        $perPage = 10;

        $itemsFetcher = $this
            ->getMockBuilder(\stdClass::class)
            ->setMethods(['__invoke'])
            ->getMock();
        $itemsFetcher
            ->expects($this->exactly(3))
            ->method('__invoke')
            ->will($this->returnValueMap([
                [0, 11, range(0, 10)],
                [11, 10, range(11, 20)],
                [21, 10, range(21, 30)]
            ]));

        $iterator = SequentialPageIterator::from($index, $perPage, $itemsFetcher);

        $this->assertEquals(range(0, 29), iterator_to_array($iterator->iterate()->take(30)));
    }

    public function testNext()
    {
        $index = 0;
        $perPage = 10;
        $items = range(10, 20);

        $itemsFetcher = $this
            ->getMockBuilder(\stdClass::class)
            ->setMethods(['__invoke'])
            ->getMock();
        $itemsFetcher
            ->expects($this->exactly(3))
            ->method('__invoke')
            ->will($this->returnValueMap([
                [0, 11, range(0, 10)],
                [11, 10, range(11, 20)],
                [21, 10, range(21, 30)]
            ]));

        $iterator = SequentialPageIterator::from($index, $perPage, $itemsFetcher);

        $this->assertEquals(range(0, 9), iterator_to_array($iterator));
        $this->assertEquals(range(10, 19), iterator_to_array($iterator = $iterator->next()));
        $this->assertEquals(range(20, 29), iterator_to_array($iterator = $iterator->next()));
    }
}
