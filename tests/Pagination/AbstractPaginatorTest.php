<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\Pagination;

use Emonkak\Orm\Pagination\AbstractPaginator;
use Emonkak\Orm\Pagination\Page;
use Emonkak\Orm\Pagination\PaginatablePageInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Emonkak\Orm\Pagination\AbstractPaginator
 */
class AbstractPaginatorTest extends TestCase
{
    public function testGetIterator(): void
    {
        $paginator = $this->getMockBuilder(AbstractPaginator::class)
            ->onlyMethods(['at', 'getPerPage', 'getTotalItems'])
            ->getMock();

        $pages = [
            new Page(new \ArrayIterator([['foo' => 123]]), 0, $paginator),
            new Page(new \ArrayIterator([['bar' => 456]]), 1, $paginator),
            new Page(new \ArrayIterator([['baz' => 789]]), 2, $paginator),
        ];

        $paginator
            ->expects($this->once())
            ->method('getTotalItems')
            ->willReturn(30);
        $paginator
            ->expects($this->once())
            ->method('getPerPage')
            ->willReturn(10);
        $paginator
            ->expects($this->exactly(3))
            ->method('at')
            ->willReturnMap([
                [0, $pages[0]],
                [1, $pages[1]],
                [2, $pages[2]],
            ]);

        $this->assertEquals([['foo' => 123], ['bar' => 456], ['baz' => 789]], iterator_to_array($paginator));
    }

    /**
     * @dataProvider providerHas
     */
    public function testHas(int $index, int $totalItems, int $perPage, bool $expectedResult): void
    {
        $paginator = $this->getMockBuilder(AbstractPaginator::class)
            ->onlyMethods(['at', 'getPerPage', 'getTotalItems'])
            ->getMock();
        $paginator
            ->expects($this->once())
            ->method('getTotalItems')
            ->willReturn($totalItems);
        $paginator
            ->expects($this->once())
            ->method('getPerPage')
            ->willReturn($perPage);

        $this->assertSame($expectedResult, $paginator->has($index));
    }

    public static function providerHas(): array
    {
        return [
            [0, 0, 10, false],
            [0, 10, 10, true],
            [1, 10, 10, false],
            [2, 10, 10, false],
            [0, 100, 10, true],
            [1, 100, 10, true],
            [9, 100, 10, true],
            [10, 100, 10, false],
            [11, 100, 10, false],
        ];
    }

    public function testFirst(): void
    {
        $page = $this->createMock(PaginatablePageInterface::class);

        $paginator = $this->getMockBuilder(AbstractPaginator::class)
            ->onlyMethods(['at', 'getPerPage', 'getTotalItems'])
            ->getMock();
        $paginator
            ->expects($this->once())
            ->method('at')
            ->with($this->identicalTo(0))
            ->willReturn($page);

        $this->assertSame($page, $paginator->firstPage());
    }

    public function testLast(): void
    {
        $page = $this->createMock(PaginatablePageInterface::class);

        $paginator = $this->getMockBuilder(AbstractPaginator::class)
            ->onlyMethods(['at', 'getPerPage', 'getTotalItems'])
            ->getMock();
        $paginator
            ->expects($this->once())
            ->method('getTotalItems')
            ->willReturn(100);
        $paginator
            ->expects($this->once())
            ->method('getPerPage')
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
    public function testGetNumPages(int $totalItems, int $perPage, int $expectedTotalPages): void
    {
        $paginator = $this->getMockBuilder(AbstractPaginator::class)
            ->onlyMethods(['at', 'getPerPage', 'getTotalItems'])
            ->getMock();
        $paginator
            ->expects($this->once())
            ->method('getTotalItems')
            ->willReturn($totalItems);
        $paginator
            ->expects($this->once())
            ->method('getPerPage')
            ->willReturn($perPage);

        $this->assertSame($expectedTotalPages, $paginator->getTotalPages());
    }

    public static function providerGetNumPages(): array
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
