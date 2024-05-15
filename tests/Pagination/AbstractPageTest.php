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
     * @dataProvider providerGetOffset
     */
    public function testGetOffset(int $index, int $perPage, int $expectedOffset): void
    {
        /** @psalm-suppress ArgumentTypeCoercion */
        $page = $this->getMockBuilder(AbstractPage::class)
            ->onlyMethods(array_values(array_diff(get_class_methods(PageInterface::class), [
                'getOffset',
            ])))
            ->getMock();
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

    public static function providerGetOffset(): array
    {
        return [
            [0, 10, 0],
            [1, 10, 10],
            [2, 10, 20],
        ];
    }

    public function testForward(): void
    {
        /** @psalm-suppress ArgumentTypeCoercion */
        $firstPage = $this->getMockBuilder(AbstractPage::class)
            ->onlyMethods(array_values(array_diff(get_class_methods(PageInterface::class), [
                'forward',
            ])))
            ->getMock();
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
        /** @psalm-suppress ArgumentTypeCoercion */
        $firstPage = $this->getMockBuilder(AbstractPage::class)
            ->onlyMethods(array_values(array_diff(get_class_methods(PageInterface::class), [
                'backward',
            ])))
            ->getMock();
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
        /** @psalm-suppress ArgumentTypeCoercion */
        $page = $this->getMockBuilder(AbstractPage::class)
            ->onlyMethods(array_values(array_diff(get_class_methods(PageInterface::class), [
                'isFirst',
            ])))
            ->getMock();
        $page
            ->expects($this->exactly(2))
            ->method('hasPrevious')
            ->willReturnOnConsecutiveCalls(false, true);

        $this->assertTrue($page->isFirst());
        $this->assertFalse($page->isFirst());
    }

    public function testIsLast(): void
    {
        /** @psalm-suppress ArgumentTypeCoercion */
        $page = $this->getMockBuilder(AbstractPage::class)
            ->onlyMethods(array_values(array_diff(get_class_methods(PageInterface::class), [
                'isLast',
            ])))
            ->getMock();
        $page
            ->expects($this->exactly(2))
            ->method('hasNext')
            ->willReturnOnConsecutiveCalls(false, true);

        $this->assertTrue($page->isLast());
        $this->assertFalse($page->isLast());
    }
}
