<?php

namespace Emonkak\Orm\Tests\Pagination;

use Emonkak\Orm\Pagination\AbstractPaginator;
use Emonkak\Orm\Pagination\Page;

/**
 * @covers Emonkak\Orm\Pagination\AbstractPaginator
 */
class AbstractPaginatorTest extends \PHPUnit_Framework_TestCase
{
    public function testGetIterator()
    {
        $pages = [
            $this->createMock(MockedPage::class),
            $this->createMock(MockedPage::class),
            $this->createMock(MockedPage::class),
        ];

        $pages[0]
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([['foo' => 123]]));
        $pages[1]
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([['bar' => 456]]));
        $pages[2]
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([['baz' => 789]]));

        $paginator = $this->getMockForAbstractClass(
            AbstractPaginator::class,
            [],
            '',
            true,
            true,
            true,
            ['getNumPages']
        );
        $paginator
            ->expects($this->once())
            ->method('getNumPages')
            ->willReturn(3);
        $paginator
            ->expects($this->exactly(3))
            ->method('at')
            ->will($this->returnValueMap([
                [0, $pages[0]],
                [1, $pages[1]],
                [2, $pages[2]]
            ]));

        $this->assertEquals([['foo' => 123], ['bar' => 456], ['baz' => 789]], iterator_to_array($paginator));
    }

    /**
     * @dataProvider providerHas
     */
    public function testHas($index, $numPages, $expectedResult)
    {
        $paginator = $this->getMockForAbstractClass(
            AbstractPaginator::class,
            [],
            '',
            true,
            true,
            true,
            ['getNumPages']
        );
        $paginator
            ->expects($this->once())
            ->method('getNumPages')
            ->willReturn($numPages);

        $this->assertSame($expectedResult, $paginator->has($index));
    }

    public function providerHas()
    {
        return [
            [0, 0, false],
            [0, 1, true],
            [1, 1, false],
            [2, 1, false],
            [0, 10, true],
            [1, 10, true],
            [9, 10, true],
            [10, 10, false],
            [11, 10, false]
        ];
    }

    public function testFirst()
    {
        $page = $this->createMock(MockedPage::class);

        $paginator = $this->getMockForAbstractClass(AbstractPaginator::class);
        $paginator
            ->expects($this->once())
            ->method('at')
            ->with($this->identicalTo(0))
            ->willReturn($page);

        $this->assertSame($page, $paginator->firstPage());
    }

    public function testLast()
    {
        $page = $this->createMock(MockedPage::class);

        $paginator = $this->getMockForAbstractClass(
            AbstractPaginator::class,
            [],
            '',
            false,
            false,
            false,
            ['getNumPages']
        );
        $paginator
            ->expects($this->once())
            ->method('getNumPages')
            ->willReturn(10);
        $paginator
            ->expects($this->once())
            ->method('at')
            ->with($this->identicalTo(9))
            ->willReturn($page);

        $this->assertSame($page, $paginator->lastPage());
    }

    /**
     * @dataProvider providerGetNumPages
     */
    public function testGetNumPages($numItems, $perPage, $expected)
    {
        $paginator = $this->getMockForAbstractClass(
            AbstractPaginator::class,
            [],
            '',
            true,
            true,
            true,
            ['getNumItems']
        );
        $paginator
            ->expects($this->once())
            ->method('getNumItems')
            ->willReturn($numItems);
        $paginator
            ->expects($this->once())
            ->method('getPerPage')
            ->willReturn($perPage);

        $this->assertSame($expected, $paginator->getNumPages());
    }

    public function providerGetNumPages()
    {
        return [
            [0, 10, 0],
            [1, 10, 1],
            [10, 10, 1],
            [11, 10, 2],
            [20, 10, 2],
            [21, 10, 3],
        ];
    }
}
