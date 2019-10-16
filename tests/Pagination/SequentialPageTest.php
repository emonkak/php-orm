<?php

namespace Emonkak\Orm\Tests\Pagination;

use Emonkak\Orm\Pagination\PaginatorInterface;
use Emonkak\Orm\Pagination\SequentialPage;

/**
 * @covers Emonkak\Orm\Pagination\SequentialPage
 */
class SequentialPageTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $perPage = 10;
        $result = range(0, 10);

        $resultFetcher = $this
            ->getMockBuilder(\stdClass::class)
            ->setMethods(['__invoke'])
            ->getMock();
        $resultFetcher
            ->expects($this->never())
            ->method('__invoke');

        $paginator = $this->createMock(PaginatorInterface::class);
        $paginator
            ->expects($this->any())
            ->method('getPerPage')
            ->willReturn($perPage);

        $page = new SequentialPage($result, 1, $paginator);

        $this->assertSame(1, $page->getIndex());
        $this->assertSame(10, $page->getOffset());
        $this->assertSame($paginator, $page->getPaginator());
        $this->assertSame(range(0, 9), iterator_to_array($page->getIterator()));
    }

    public function testNext()
    {
        $perPage = 10;
        $results = [
            range(0, 10),
            range(10, 19),
        ];

        $resultFetcher = $this
            ->getMockBuilder(\stdClass::class)
            ->setMethods(['__invoke'])
            ->getMock();
        $resultFetcher
            ->expects($this->never())
            ->method('__invoke');

        $paginator = $this->createMock(PaginatorInterface::class);
        $paginator
            ->expects($this->any())
            ->method('getPerPage')
            ->willReturn($perPage);
        $paginator
            ->expects($this->once())
            ->method('at')
            ->with($this->identicalTo(1))
            ->willReturn($nextPage = new SequentialPage($results[1], 1, $paginator));

        $page = new SequentialPage($results[0], 0, $paginator);

        $this->assertSame($nextPage, $page->next());
        $this->assertTrue($page->hasNext());
        $this->assertTrue($page->isFirst());
        $this->assertFalse($page->isLast());
        $this->assertEmpty(iterator_to_array($nextPage->next()));
        $this->assertFalse($nextPage->hasNext());
        $this->assertFalse($nextPage->isFirst());
        $this->assertTrue($nextPage->isLast());
    }

    public function testPrevious()
    {
        $perPage = 10;
        $results = [
            range(0, 10),
            range(10, 19),
        ];

        $resultFetcher = $this
            ->getMockBuilder(\stdClass::class)
            ->setMethods(['__invoke'])
            ->getMock();
        $resultFetcher
            ->expects($this->never())
            ->method('__invoke');

        $paginator = $this->createMock(PaginatorInterface::class);
        $paginator
            ->expects($this->any())
            ->method('getPerPage')
            ->willReturn($perPage);
        $paginator
            ->expects($this->once())
            ->method('at')
            ->with($this->identicalTo(0))
            ->willReturn($previousPage = new SequentialPage($results[0], 0, $paginator));

        $page = new SequentialPage($results[1], 1, $paginator);

        $this->assertSame($previousPage, $page->previous());
        $this->assertTrue($page->hasPrevious());
        $this->assertFalse($page->isFirst());
        $this->assertTrue($page->isLast());
        $this->assertEmpty(iterator_to_array($previousPage->previous()));
        $this->assertFalse($previousPage->hasPrevious());
        $this->assertTrue($previousPage->isFirst());
        $this->assertFalse($previousPage->isLast());
    }
}
