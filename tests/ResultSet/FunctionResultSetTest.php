<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\ResultSet;

use Emonkak\Orm\ResultSet\FunctionResultSet;
use Emonkak\Orm\Tests\Fixtures\IterablePDOStatementInterface;
use Emonkak\Orm\Tests\Fixtures\Model;
use PHPUnit\Framework\TestCase;

/**
 * @covers Emonkak\Orm\ResultSet\FunctionResultSet
 */
class FunctionResultSetTest extends TestCase
{
    private $stmt;

    private $result;

    public function setUp(): void
    {
        $this->stmt = $this->createMock(IterablePDOStatementInterface::class);
        $this->result = new FunctionResultSet($this->stmt, function($props) {
            return new Model($props);
        }, Model::class);
    }

    public function testGetClass(): void
    {
        $this->assertSame(Model::class, $this->result->getClass());
    }

    public function testGetIterator(): void
    {
        $this->stmt
            ->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $this->stmt
            ->expects($this->once())
            ->method('setFetchMode')
            ->with(\PDO::FETCH_ASSOC)
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

        $expected = [
            new Model(['foo' => 1]),
            new Model(['foo' => 2]),
            new Model(['foo' => 3]),
            new Model(['foo' => 4]),
        ];

        $this->assertEquals($expected, iterator_to_array($this->result));
    }

    public function testToArray(): void
    {
        $this->stmt
            ->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $this->stmt
            ->expects($this->once())
            ->method('fetchAll')
            ->with(\PDO::FETCH_ASSOC)
            ->willReturn([['foo' => 123], ['foo' => 456]]);

        $expected = [new Model(['foo' => 123]), new Model(['foo' => 456])];

        $this->assertEquals($expected, $this->result->toArray());
    }

    public function testFirst(): void
    {
        $this->stmt
            ->expects($this->exactly(2))
            ->method('execute')
            ->willReturn(true);
        $this->stmt
            ->expects($this->exactly(2))
            ->method('fetch')
            ->with(\PDO::FETCH_ASSOC)
            ->willReturn(['foo' => 123, 'bar' => 345]);

        $expected = new Model(['foo' => 123, 'bar' => 345]);

        $this->assertEquals($expected, $this->result->first());
        $this->assertEquals($expected, $this->result->firstOrDefault());
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
            ->with(\PDO::FETCH_ASSOC)
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
            ->with(\PDO::FETCH_ASSOC)
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
            ->with(\PDO::FETCH_ASSOC)
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

        $expected = [
            new Model(['foo' => 1]),
            new Model(['foo' => 2]),
            new Model(['foo' => 3]),
            new Model(['foo' => 4]),
        ];
        $predicate = function($value) {
            return $value->foo % 2 === 0;
        };

        $this->assertEquals(new Model(['foo' => 2]), $this->result->first($predicate));
        $this->assertEquals(new Model(['foo' => 2]), $this->result->firstOrDefault($predicate));
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
            ->with(\PDO::FETCH_ASSOC)
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

    public function testFirstWithPredicateReturnsDefaultValue(): void
    {
        $this->stmt
            ->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $this->stmt
            ->expects($this->once())
            ->method('setFetchMode')
            ->with(\PDO::FETCH_ASSOC)
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
