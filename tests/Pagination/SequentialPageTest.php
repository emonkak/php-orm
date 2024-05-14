<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\Pagination;

use Emonkak\Orm\Pagination\SequentialPage;
use Emonkak\Orm\Tests\Fixtures\Spy;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Emonkak\Orm\Pagination\SequentialPage
 */
class SequentialPageTest extends TestCase
{
    public function testFrom(): void
    {
        $initialIndex = 1;
        $perPage = 10;
        $items = range(10, 20);
        $expectedItems = range(10, 19);

        $itemsFetcher = $this->createMock(Spy::class);
        $itemsFetcher
            ->expects($this->once())
            ->method('__invoke')
            ->willReturnMap([
                [10, 11, $items],
            ]);

        $page = SequentialPage::from($initialIndex, $perPage, $itemsFetcher);

        $this->assertSame($perPage, $page->getPerPage());
        $this->assertSame($initialIndex, $page->getIndex());
        $this->assertSame(10, $page->getOffset());
        $this->assertSame($expectedItems, $page->getItems());
        $this->assertSame($expectedItems, $page->getSource());
        $this->assertSame($expectedItems, iterator_to_array($page));
        $this->assertTrue($page->hasNext());
        $this->assertTrue($page->hasPrevious());
        $this->assertFalse($page->isFirst());
        $this->assertFalse($page->isLast());
    }

    public function testPrevious(): void
    {
        $initialIndex = 2;
        $perPage = 10;

        $itemsFetcher = $this->createMock(Spy::class);
        $itemsFetcher
            ->expects($this->exactly(3))
            ->method('__invoke')
            ->willReturnMap([
                [20, 11, range(20, 29)],
                [10, 10, range(10, 19)],
                [0, 10, range(0, 9)],
            ]);

        $page = SequentialPage::from($initialIndex, $perPage, $itemsFetcher);
        $this->assertSame(range(20, 29), iterator_to_array($page));
        $this->assertSame($initialIndex, $page->getIndex());
        $this->assertFalse($page->hasNext());
        $this->assertTrue($page->hasPrevious());
        $this->assertFalse($page->isFirst());
        $this->assertTrue($page->isLast());

        $page = $page->previous();
        $this->assertSame(range(10, 19), iterator_to_array($page));
        $this->assertSame($initialIndex - 1, $page->getIndex());
        $this->assertTrue($page->hasNext());
        $this->assertTrue($page->hasPrevious());
        $this->assertFalse($page->isFirst());
        $this->assertFalse($page->isLast());

        $page = $page->previous();
        $this->assertSame(range(0, 9), iterator_to_array($page));
        $this->assertSame($initialIndex - 2, $page->getIndex());
        $this->assertTrue($page->hasNext());
        $this->assertFalse($page->hasPrevious());
        $this->assertTrue($page->isFirst());
        $this->assertFalse($page->isLast());

        $page = $page->previous();
        $this->assertSame([], iterator_to_array($page));
        $this->assertSame($initialIndex - 3, $page->getIndex());
        $this->assertTrue($page->hasNext());
        $this->assertFalse($page->hasPrevious());
        $this->assertTrue($page->isFirst());
        $this->assertFalse($page->isLast());
    }

    public function testNext(): void
    {
        $initialIndex = 0;
        $perPage = 10;

        $itemsFetcher = $this->createMock(Spy::class);
        $itemsFetcher
            ->expects($this->exactly(3))
            ->method('__invoke')
            ->willReturnMap([
                [0, 11, range(0, 10)],
                [11, 10, range(11, 20)],
                [21, 10, range(21, 29)],
            ]);

        $page = SequentialPage::from($initialIndex, $perPage, $itemsFetcher);
        $this->assertSame(range(0, 9), iterator_to_array($page));
        $this->assertSame($initialIndex, $page->getIndex());
        $this->assertTrue($page->hasNext());
        $this->assertFalse($page->hasPrevious());
        $this->assertTrue($page->isFirst());
        $this->assertFalse($page->isLast());

        $page = $page->next();
        $this->assertSame(range(10, 19), iterator_to_array($page));
        $this->assertSame($initialIndex + 1, $page->getIndex());
        $this->assertTrue($page->hasNext());
        $this->assertTrue($page->hasPrevious());
        $this->assertFalse($page->isFirst());
        $this->assertFalse($page->isLast());

        $page = $page->next();
        $this->assertSame(range(20, 29), iterator_to_array($page));
        $this->assertSame($initialIndex + 2, $page->getIndex());
        $this->assertFalse($page->hasNext());
        $this->assertTrue($page->hasPrevious());
        $this->assertFalse($page->isFirst());
        $this->assertTrue($page->isLast());

        $page = $page->next();
        $this->assertSame([], iterator_to_array($page));
        $this->assertSame($initialIndex + 3, $page->getIndex());
        $this->assertFalse($page->hasNext());
        $this->assertTrue($page->hasPrevious());
        $this->assertFalse($page->isFirst());
        $this->assertTrue($page->isLast());
    }
}
