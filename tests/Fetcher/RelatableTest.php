<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\Fetcher;

use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\Fetcher\Relatable;
use Emonkak\Orm\Fetcher\RelationFetcher;
use Emonkak\Orm\Relation\RelationInterface;
use Emonkak\Orm\Tests\Fixtures\Spy;
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

        $relationFactory = $this->createMock(Spy::class);
        $relationFactory
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($class))
            ->willReturn($relation);

        $relationFetcher = $relatable->with($relationFactory);

        $this->assertInstanceOf(RelationFetcher::class, $relationFetcher);
        $this->assertSame($relatable, $relationFetcher->getFetcher());
        $this->assertSame($relation, $relationFetcher->getRelation());
    }

    public function testWithRelation(): void
    {
        $class = \stdClass::class;

        $relation = $this->createMock(RelationInterface::class);

        $relatable = $this->getMockBuilder(RelatableMock::class)
            ->onlyMethods(['getClass', 'getPdo', 'fetch'])
            ->getMock();

        $relationFetcher = $relatable->withRelation($relation);

        $this->assertInstanceOf(RelationFetcher::class, $relationFetcher);
        $this->assertSame($relatable, $relationFetcher->getFetcher());
        $this->assertSame($relation, $relationFetcher->getRelation());
    }
}

abstract class RelatableMock implements FetcherInterface
{
    use Relatable;
}
