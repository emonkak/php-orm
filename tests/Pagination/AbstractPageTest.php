<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\Pagination;

use Emonkak\Orm\Pagination\AbstractPage;
use Emonkak\Orm\Pagination\PageInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Emonkak\Orm\Pagination\AbstractPage
 */
class AbstractPageTest extends TestCase
{
    /**
     * @dataProvider prividerGetOffset
     */
    public function testGetOffset($index, $perPage, $expectedOffset): void
    {
        $page = $this->getMockForAbstractClass(AbstractPage::class);
        $page
            ->expects($this->once())
            ->method('getIndex')
            ->willReturn($index);
        $page
            ->expects($this->once())
            ->method('getPerPage')
            ->willReturn($perPage);

        $this->assertSame($expectedOffset, $page->getOffset());
    }

    public function prividerGetOffset(): array
    {
        return [
            [0, 10, 0],
            [1, 10, 10],
            [2, 10, 20],
        ];
    }

    public function testForward(): void
    {
        $firstPage = $this->getMockForAbstractClass(AbstractPage::class);
        $secondPage = $this->createMock(PageInterface::class);
        $thirdPage = $this->createMock(PageInterface::class);

        $firstPage
            ->expects($this->once())
            ->method('hasNext')
            ->willReturn(true);
        $firstPage
            ->expects($this->once())
            ->method('next')
            ->willReturn($secondPage);

        $secondPage
            ->expects($this->once())
            ->method('hasNext')
            ->willReturn(true);
        $secondPage
            ->expects($this->once())
            ->method('next')
            ->willReturn($thirdPage);

        $thirdPage
            ->expects($this->once())
            ->method('hasNext')
            ->willReturn(false);
        $thirdPage
            ->expects($this->never())
            ->method('next');

        $this->assertSame([$firstPage, $secondPage, $thirdPage], iterator_to_array($firstPage->forward()));
    }

    public function testBackward(): void
    {
        $firstPage = $this->getMockForAbstractClass(AbstractPage::class);
        $secondPage = $this->createMock(PageInterface::class);
        $thirdPage = $this->createMock(PageInterface::class);

        $firstPage
            ->expects($this->once())
            ->method('hasPrevious')
            ->willReturn(true);
        $firstPage
            ->expects($this->once())
            ->method('previous')
            ->willReturn($secondPage);

        $secondPage
            ->expects($this->once())
            ->method('hasPrevious')
            ->willReturn(true);
        $secondPage
            ->expects($this->once())
            ->method('previous')
            ->willReturn($thirdPage);

        $thirdPage
            ->expects($this->once())
            ->method('hasPrevious')
            ->willReturn(false);
        $thirdPage
            ->expects($this->never())
            ->method('previous');

        $this->assertSame([$firstPage, $secondPage, $thirdPage], iterator_to_array($firstPage->backward()));
    }

    public function testIsFirst(): void
    {
        $page = $this->getMockForAbstractClass(AbstractPage::class);
        $page
            ->expects($this->exactly(2))
            ->method('hasPrevious')
            ->willReturnOnConsecutiveCalls(false, true);

        $this->assertTrue($page->isFirst());
        $this->assertFalse($page->isFirst());
    }

    public function testIsLast(): void
    {
        $page = $this->getMockForAbstractClass(AbstractPage::class);
        $page
            ->expects($this->exactly(2))
            ->method('hasNext')
            ->willReturnOnConsecutiveCalls(false, true);

        $this->assertTrue($page->isLast());
        $this->assertFalse($page->isLast());
    }
}
