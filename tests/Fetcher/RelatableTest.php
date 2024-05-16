<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\Fetcher;

use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\Fetcher\Relatable;
use Emonkak\Orm\Fetcher\RelationFetcher;
use Emonkak\Orm\Relation\RelationInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Emonkak\Orm\Fetcher\Relatable
 */
class RelatableTest extends TestCase
{
    public function testWith(): void
    {
        $class = \stdClass::class;

        $relatable = $this->getMockBuilder(RelatableMock::class)
            ->onlyMethods(['getClass', 'getPdo', 'fetch'])
            ->getMock();
        $relatable
            ->expects($this->once())
            ->method('getClass')
            ->willReturn($class);

        $relation = $this->createMock(RelationInterface::class);

        $fetcher = $relatable->with(function() use ($relation) {
            return $relation;
        });

        $this->assertInstanceOf(RelationFetcher::class, $fetcher);
        $this->assertSame($relatable, $fetcher->getFetcher());
        $this->assertSame($relation, $fetcher->getRelation());
    }

    public function testWithRelation(): void
    {
        $class = \stdClass::class;
        $relation = $this->createMock(RelationInterface::class);

        $relatable = $this->getMockBuilder(RelatableMock::class)
            ->onlyMethods(['getClass', 'getPdo', 'fetch'])
            ->getMock();

        $fetcher = $relatable->withRelation($relation);

        $this->assertInstanceOf(RelationFetcher::class, $fetcher);
        $this->assertSame($relatable, $fetcher->getFetcher());
        $this->assertSame($relation, $fetcher->getRelation());
    }
}

/**
 * @template T
 * @implements FetcherInterface<T>
 */
abstract class RelatableMock implements FetcherInterface
{
    /**
     * @use Relatable<T>
     */
    use Relatable;
}
