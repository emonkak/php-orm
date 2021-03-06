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
        $paginator = $this->getMockForAbstractClass(
            AbstractPaginator::class,
            [],
            '',
            true,
            true,
            true,
            ['getTotalPages']
        );

        $pages = [
            new Page(new \ArrayIterator([['foo' => 123]]), 0, $paginator),
            new Page(new \ArrayIterator([['bar' => 456]]), 1, $paginator),
            new Page(new \ArrayIterator([['baz' => 789]]), 2, $paginator),
        ];

        $paginator
            ->expects($this->once())
            ->method('getTotalPages')
            ->willReturn(3);
        $paginator
            ->expects($this->exactly(3))
            ->method('at')
            ->will($this->returnValueMap([
                [0, $pages[0]],
                [1, $pages[1]],
                [2, $pages[2]],
            ]));

        $this->assertEquals([['foo' => 123], ['bar' => 456], ['baz' => 789]], iterator_to_array($paginator));
    }

    /**
     * @dataProvider providerHas
     */
    public function testHas($index, $totalPages, $expectedResult): void
    {
        $paginator = $this->getMockForAbstractClass(
            AbstractPaginator::class,
            [],
            '',
            true,
            true,
            true,
            ['getTotalPages']
        );
        $paginator
            ->expects($this->once())
            ->method('getTotalPages')
            ->willReturn($totalPages);

        $this->assertSame($expectedResult, $paginator->has($index));
    }

    public function providerHas(): array
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
            [11, 10, false],
        ];
    }

    public function testFirst(): void
    {
        $page = $this->createMock(PaginatablePageInterface::class);

        $paginator = $this->getMockForAbstractClass(AbstractPaginator::class);
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

        $paginator = $this->getMockForAbstractClass(
            AbstractPaginator::class,
            [],
            '',
            false,
            false,
            false,
            ['getTotalPages']
        );
        $paginator
            ->expects($this->once())
            ->method('getTotalPages')
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
    public function testGetNumPages($totalItems, $perPage, $expected): void
    {
        $paginator = $this->getMockForAbstractClass(
            AbstractPaginator::class,
            [],
            '',
            true,
            true,
            true,
            ['getTotalItems']
        );
        $paginator
            ->expects($this->once())
            ->method('getTotalItems')
            ->willReturn($totalItems);
        $paginator
            ->expects($this->once())
            ->method('getPerPage')
            ->willReturn($perPage);

        $this->assertSame($expected, $paginator->getTotalPages());
    }

    public function providerGetNumPages(): array
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
