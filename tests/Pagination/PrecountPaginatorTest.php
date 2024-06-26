<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\Pagination;

use Emonkak\Orm\Pagination\PrecountPaginator;
use Emonkak\Orm\Tests\Fixtures\Spy;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Emonkak\Orm\Pagination\PrecountPaginator
 */
class PrecountPaginatorTest extends TestCase
{
    public function testAt(): void
    {
        $perPage = 10;
        $totalItems = 21;

        $results = [
            array_fill(0, 10, new \stdClass()),
            array_fill(0, 10, new \stdClass()),
            array_fill(0, 1, new \stdClass()),
        ];

        $itemsFetcher = $this->createMock(Spy::class);
        $itemsFetcher
            ->expects($this->any())
            ->method('__invoke')
            ->willReturnMap([
                [0, 10, new \ArrayIterator($results[0])],
                [10, 10, new \ArrayIterator($results[1])],
                [20, 10, new \ArrayIterator($results[2])],
            ]);

        /** @var callable(int, int):\Traversable<int,\stdClass> $itemsFetcher */
        $paginator = new PrecountPaginator($perPage, $totalItems, $itemsFetcher);

        $this->assertSame($results[0], iterator_to_array($paginator->firstPage()));
        $this->assertSame($results[2], iterator_to_array($paginator->lastPage()));
        $this->assertSame($results[0], iterator_to_array($paginator->at(0)));
        $this->assertSame($results[1], iterator_to_array($paginator->at(1)));
        $this->assertSame($results[2], iterator_to_array($paginator->at(2)));
        $this->assertEmpty(iterator_to_array($paginator->at(99)));
        $this->assertSame(array_merge(...$results), iterator_to_array($paginator));
    }
}
