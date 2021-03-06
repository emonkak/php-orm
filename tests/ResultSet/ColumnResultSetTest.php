<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\ResultSet;

use Emonkak\Orm\ResultSet\ColumnResultSet;
use Emonkak\Orm\Tests\Fixtures\IterablePDOStatementInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Emonkak\Orm\ResultSet\ColumnResultSet
 */
class ColumnResultSetTest extends TestCase
{
    private $stmt;

    private $result;

    public function setUp(): void
    {
        $this->stmt = $this->createMock(IterablePDOStatementInterface::class);
        $this->result = new ColumnResultSet($this->stmt, 1);
    }

    public function testGetIterator(): void
    {
        $expected = [['foo' => 123], ['foo' => 345]];

        $this->stmt
            ->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $this->stmt
            ->expects($this->once())
            ->method('setFetchMode')
            ->with(\PDO::FETCH_COLUMN, 1)
            ->willReturn(true);
        $this->stmt
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator($expected));

        $this->assertSame($expected, iterator_to_array($this->result));
    }

    public function testToColumn(): void
    {
        $expected = [['foo' => 123], ['foo' => 345]];

        $this->stmt
            ->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $this->stmt
            ->expects($this->once())
            ->method('fetchAll')
            ->with(\PDO::FETCH_COLUMN, 1)
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
            ->method('fetch')
            ->with(\PDO::FETCH_COLUMN, 1)
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
            ->method('fetch')
            ->with(\PDO::FETCH_COLUMN, 1)
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
            ->method('fetch')
            ->with(\PDO::FETCH_COLUMN, 1)
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
            ->with(\PDO::FETCH_COLUMN, 1)
            ->willReturn(true);
        $this->stmt
            ->expects($this->exactly(2))
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([
                ['foo' => 1],
                ['foo' => 2],
                ['foo' => 3],
                ['foo' => 4],
            ]));

        $predicate = function($value) {
            return $value['foo'] % 2 === 0;
        };

        $this->assertEquals(['foo' => 2], $this->result->first($predicate));
        $this->assertEquals(['foo' => 2], $this->result->firstOrDefault($predicate));
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
            ->with(\PDO::FETCH_COLUMN, 1)
            ->willReturn(true);
        $this->stmt
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([
                ['foo' => 1],
                ['foo' => 2],
                ['foo' => 3],
                ['foo' => 4],
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
            ->with(\PDO::FETCH_COLUMN, 1)
            ->willReturn(true);
        $this->stmt
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([
                ['foo' => 1],
                ['foo' => 2],
                ['foo' => 3],
                ['foo' => 4],
            ]));

        $predicate = function($value) {
            return false;
        };

        $this->assertNull($this->result->firstOrDefault($predicate));
    }
}
