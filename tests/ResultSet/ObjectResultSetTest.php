<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\ResultSet;

use Emonkak\Orm\ResultSet\ObjectResultSet;
use Emonkak\Orm\Tests\Fixtures\IterablePDOStatementInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Emonkak\Orm\ResultSet\ObjectResultSet
 */
class ObjectResultSetTest extends TestCase
{
    public function testGetClass(): void
    {
        $stmt = $this->createMock(IterablePDOStatementInterface::class);
        $result = new ObjectResultSet($stmt, \stdClass::class, ['foo']);

        $this->assertSame(\stdClass::class, $result->getClass());
    }

    public function testGetConstructorArguments(): void
    {
        $stmt = $this->createMock(IterablePDOStatementInterface::class);
        $result = new ObjectResultSet($stmt, \stdClass::class, ['foo']);

        $this->assertSame(['foo'], $result->getConstructorArguments());
    }

    public function testGetIterator(): void
    {
        $expectedResult = [(object) ['foo' => 123], (object) ['foo' => 345]];

        $stmt = $this->createMock(IterablePDOStatementInterface::class);
        $stmt
            ->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $stmt
            ->expects($this->once())
            ->method('setFetchMode')
            ->with(\PDO::FETCH_CLASS, \stdClass::class, ['foo'])
            ->willReturn(true);
        $stmt
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($expectedResult));
        $result = new ObjectResultSet($stmt, \stdClass::class, ['foo']);

        $this->assertSame($expectedResult, iterator_to_array($result));
    }

    public function testToArray(): void
    {
        $expectedResult = [(object) ['foo' => 123], (object) ['foo' => 345]];

        $stmt = $this->createMock(IterablePDOStatementInterface::class);
        $stmt
            ->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $stmt
            ->expects($this->once())
            ->method('fetchAll')
            ->with(\PDO::FETCH_CLASS, \stdClass::class, ['foo'])
            ->willReturn($expectedResult);
        $result = new ObjectResultSet($stmt, \stdClass::class, ['foo']);

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
            ->method('setFetchMode')
            ->with(\PDO::FETCH_CLASS, \stdClass::class, ['foo'])
            ->willReturn(true);
        $stmt
            ->expects($this->exactly(2))
            ->method('fetch')
            ->with()
            ->willReturn($expectedResult);
        $result = new ObjectResultSet($stmt, \stdClass::class, ['foo']);

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
            ->method('setFetchMode')
            ->with(\PDO::FETCH_CLASS, \stdClass::class, ['foo'])
            ->willReturn(true);
        $stmt
            ->expects($this->once())
            ->method('fetch')
            ->with()
            ->willReturn(false);
        $result = new ObjectResultSet($stmt, \stdClass::class, ['foo']);

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
            ->method('setFetchMode')
            ->with(\PDO::FETCH_CLASS, \stdClass::class, ['foo'])
            ->willReturn(true);
        $stmt
            ->expects($this->once())
            ->method('fetch')
            ->with()
            ->willReturn(false);
        $result = new ObjectResultSet($stmt, \stdClass::class, ['foo']);

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
            ->with(\PDO::FETCH_CLASS, \stdClass::class, ['foo'])
            ->willReturn(true);
        $stmt
            ->expects($this->exactly(2))
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([
                (object) ['foo' => 1],
                (object) ['foo' => 2],
                (object) ['foo' => 3],
                (object) ['foo' => 4],
            ]));
        $result = new ObjectResultSet($stmt, \stdClass::class, ['foo']);

        $predicate = function(\stdClass $value): bool {
            return $value->foo % 2 === 0;
        };

        $this->assertEquals((object) ['foo' => 2], $result->first($predicate));
        $this->assertEquals((object) ['foo' => 2], $result->firstOrDefault($predicate));
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
            ->with(\PDO::FETCH_CLASS, \stdClass::class, ['foo'])
            ->willReturn(true);
        $stmt
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([
                (object) ['foo' => 1],
                (object) ['foo' => 2],
                (object) ['foo' => 3],
                (object) ['foo' => 4],
            ]));
        $result = new ObjectResultSet($stmt, \stdClass::class, ['foo']);

        $predicate = function(\stdClass $value): bool {
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
            ->with(\PDO::FETCH_CLASS, \stdClass::class, ['foo'])
            ->willReturn(true);
        $stmt
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([
                (object) ['foo' => 1],
                (object) ['foo' => 2],
                (object) ['foo' => 3],
                (object) ['foo' => 4],
            ]));
        $result = new ObjectResultSet($stmt, \stdClass::class, ['foo']);

        $predicate = function(\stdClass $value): bool {
            return false;
        };

        $this->assertNull($result->firstOrDefault($predicate));
    }
}
