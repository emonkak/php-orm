<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\ResultSet;

use Emonkak\Orm\ResultSet\ArrayResultSet;
use Emonkak\Orm\Tests\Fixtures\IterablePDOStatementInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Emonkak\Orm\ResultSet\ArrayResultSet
 */
class ArrayResultSetTest extends TestCase
{
    public function testGetIterator(): void
    {
        $expectedResult = [['foo' => 123], ['foo' => 345]];

        $stmt = $this->createMock(IterablePDOStatementInterface::class);
        $stmt
            ->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $stmt
            ->expects($this->once())
            ->method('setFetchMode')
            ->with(\PDO::FETCH_ASSOC)
            ->willReturn(true);
        $stmt
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($expectedResult));
        $result = new ArrayResultSet($stmt);

        $this->assertSame($expectedResult, iterator_to_array($result));
    }

    public function testToArray(): void
    {
        $expectedResult = [['foo' => 123], ['foo' => 345]];

        $stmt = $this->createMock(IterablePDOStatementInterface::class);
        $stmt
            ->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $stmt
            ->expects($this->once())
            ->method('fetchAll')
            ->with(\PDO::FETCH_ASSOC)
            ->willReturn($expectedResult);
        $result = new ArrayResultSet($stmt);

        $this->assertSame($expectedResult, $result->toArray());
    }

    public function testFirst(): void
    {
        $expectedResult = ['foo' => 123, 'bar' => 345];

        $stmt = $this->createMock(IterablePDOStatementInterface::class);
        $stmt
            ->expects($this->exactly(2))
            ->method('execute')
            ->willReturn(true);
        $stmt
            ->expects($this->exactly(2))
            ->method('fetch')
            ->with(\PDO::FETCH_ASSOC)
            ->willReturn($expectedResult);
        $result = new ArrayResultSet($stmt);

        $this->assertSame($expectedResult, $result->first());
        $this->assertSame($expectedResult, $result->firstOrDefault());
    }

    public function testFirstThrowsRuntimeException(): void
    {
        $this->expectException(\RuntimeException::class);

        $stmt = $this->createMock(IterablePDOStatementInterface::class);
        $stmt
            ->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $stmt
            ->expects($this->once())
            ->method('fetch')
            ->with(\PDO::FETCH_ASSOC)
            ->willReturn(false);
        $result = new ArrayResultSet($stmt);

        $result->first();
    }

    public function testFirstOrDefaultReturnsDefaultValue(): void
    {
        $stmt = $this->createMock(IterablePDOStatementInterface::class);
        $stmt
            ->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $stmt
            ->expects($this->once())
            ->method('fetch')
            ->with(\PDO::FETCH_ASSOC)
            ->willReturn(false);
        $result = new ArrayResultSet($stmt);

        $this->assertNull($result->firstOrDefault());
    }

    public function testFirstWithPredicate(): void
    {
        $stmt = $this->createMock(IterablePDOStatementInterface::class);
        $stmt
            ->expects($this->exactly(2))
            ->method('execute')
            ->willReturn(true);
        $stmt
            ->expects($this->exactly(2))
            ->method('setFetchMode')
            ->with(\PDO::FETCH_ASSOC)
            ->willReturn(true);
        $stmt
            ->expects($this->exactly(2))
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([
                ['foo' => 1],
                ['foo' => 2],
                ['foo' => 3],
                ['foo' => 4],
            ]));
        $result = new ArrayResultSet($stmt);

        $predicate = function(array $value): bool {
            return $value['foo'] % 2 === 0;
        };

        $this->assertEquals(['foo' => 2], $result->first($predicate));
        $this->assertEquals(['foo' => 2], $result->firstOrDefault($predicate));
    }

    public function testFirstWithPredicateThrowsRuntimeException(): void
    {
        $this->expectException(\RuntimeException::class);

        $stmt = $this->createMock(IterablePDOStatementInterface::class);
        $stmt
            ->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $stmt
            ->expects($this->once())
            ->method('setFetchMode')
            ->with(\PDO::FETCH_ASSOC)
            ->willReturn(true);
        $stmt
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([
                ['foo' => 1],
                ['foo' => 2],
                ['foo' => 3],
                ['foo' => 4],
            ]));
        $result = new ArrayResultSet($stmt);

        $predicate = function(array $value): bool {
            return false;
        };

        $result->first($predicate);
    }

    public function testFirstOrDefaultWithPredicateReturnsDefaultValue(): void
    {
        $stmt = $this->createMock(IterablePDOStatementInterface::class);
        $stmt
            ->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $stmt
            ->expects($this->once())
            ->method('setFetchMode')
            ->with(\PDO::FETCH_ASSOC)
            ->willReturn(true);
        $stmt
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([
                ['foo' => 1],
                ['foo' => 2],
                ['foo' => 3],
                ['foo' => 4],
            ]));
        $result = new ArrayResultSet($stmt);

        $predicate = function(array $value): bool {
            return false;
        };

        $this->assertNull($result->firstOrDefault($predicate));
    }
}
