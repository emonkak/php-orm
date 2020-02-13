<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\ResultSet;

use Emonkak\Orm\ResultSet\ObjectResultSet;
use Emonkak\Orm\Tests\Fixtures\IterablePDOStatementInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers Emonkak\Orm\ResultSet\ObjectResultSet
 */
class ObjectResultSetTest extends TestCase
{
    private $stmt;

    private $result;

    public function setUp(): void
    {
        $this->stmt = $this->createMock(IterablePDOStatementInterface::class);
        $this->result = new ObjectResultSet($this->stmt, \stdClass::class, ['foo']);
    }

    public function testGetClass(): void
    {
        $this->assertSame(\stdClass::class, $this->result->getClass());
    }

    public function testGetConstructorArguments(): void
    {
        $this->assertSame(['foo'], $this->result->getConstructorArguments());
    }

    public function testGetIterator(): void
    {
        $expected = [(object) ['foo' => 123], (object) ['foo' => 345]];

        $this->stmt
            ->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $this->stmt
            ->expects($this->once())
            ->method('setFetchMode')
            ->with(\PDO::FETCH_CLASS, \stdClass::class, ['foo'])
            ->willReturn(true);
        $this->stmt
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($expected));

        $this->assertSame($expected, iterator_to_array($this->result));
    }

    public function testToArray(): void
    {
        $expected = [(object) ['foo' => 123], (object) ['foo' => 345]];

        $this->stmt
            ->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $this->stmt
            ->expects($this->once())
            ->method('fetchAll')
            ->with(\PDO::FETCH_CLASS, \stdClass::class, ['foo'])
            ->willReturn($expected);

        $this->assertSame($expected, $this->result->toArray());
    }

    public function testFirst(): void
    {
        $expected = ['foo' => 123, 'bar' => 345];

        $this->stmt
            ->expects($this->exactly(2))
            ->method('execute')
            ->willReturn(true);
        $this->stmt
            ->expects($this->exactly(2))
            ->method('setFetchMode')
            ->with(\PDO::FETCH_CLASS, \stdClass::class, ['foo'])
            ->willReturn(true);
        $this->stmt
            ->expects($this->exactly(2))
            ->method('fetch')
            ->with()
            ->willReturn($expected);

        $this->assertSame($expected, $this->result->first());
        $this->assertSame($expected, $this->result->firstOrDefault());
    }

    public function testFirstThrowsRuntimeException(): void
    {
        $this->expectException(\RuntimeException::class);

        $this->stmt
            ->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $this->stmt
            ->expects($this->once())
            ->method('setFetchMode')
            ->with(\PDO::FETCH_CLASS, \stdClass::class, ['foo'])
            ->willReturn(true);
        $this->stmt
            ->expects($this->once())
            ->method('fetch')
            ->with()
            ->willReturn(false);

        $this->result->first();
    }

    public function testFirstOrDefaultReturnsDefaultValue(): void
    {
        $this->stmt
            ->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $this->stmt
            ->expects($this->once())
            ->method('setFetchMode')
            ->with(\PDO::FETCH_CLASS, \stdClass::class, ['foo'])
            ->willReturn(true);
        $this->stmt
            ->expects($this->once())
            ->method('fetch')
            ->with()
            ->willReturn(false);

        $this->assertNull($this->result->firstOrDefault());
    }

    public function testFirstWithPredicate(): void
    {
        $this->stmt
            ->expects($this->exactly(2))
            ->method('execute')
            ->willReturn(true);
        $this->stmt
            ->expects($this->exactly(2))
            ->method('setFetchMode')
            ->with(\PDO::FETCH_CLASS, \stdClass::class, ['foo'])
            ->willReturn(true);
        $this->stmt
            ->expects($this->exactly(2))
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([
                (object) ['foo' => 1],
                (object) ['foo' => 2],
                (object) ['foo' => 3],
                (object) ['foo' => 4],
            ]));

        $predicate = function($value) {
            return $value->foo % 2 === 0;
        };

        $this->assertEquals((object) ['foo' => 2], $this->result->first($predicate));
        $this->assertEquals((object) ['foo' => 2], $this->result->firstOrDefault($predicate));
    }

    public function testFirstWithPredicateThrowsRuntimeException(): void
    {
        $this->expectException(\RuntimeException::class);

        $this->stmt
            ->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $this->stmt
            ->expects($this->once())
            ->method('setFetchMode')
            ->with(\PDO::FETCH_CLASS, \stdClass::class, ['foo'])
            ->willReturn(true);
        $this->stmt
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([
                (object) ['foo' => 1],
                (object) ['foo' => 2],
                (object) ['foo' => 3],
                (object) ['foo' => 4],
            ]));

        $predicate = function($value) {
            return false;
        };

        $this->result->first($predicate);
    }

    public function testFirstOrDefaultWithPredicateReturnsDefaultValue(): void
    {
        $this->stmt
            ->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $this->stmt
            ->expects($this->once())
            ->method('setFetchMode')
            ->with(\PDO::FETCH_CLASS, \stdClass::class, ['foo'])
            ->willReturn(true);
        $this->stmt
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([
                (object) ['foo' => 1],
                (object) ['foo' => 2],
                (object) ['foo' => 3],
                (object) ['foo' => 4],
            ]));

        $predicate = function($value) {
            return false;
        };

        $this->assertNull($this->result->firstOrDefault($predicate));
    }
}
