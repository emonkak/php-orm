<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\ResultSet;

use Emonkak\Orm\ResultSet\FunctionResultSet;
use Emonkak\Orm\Tests\Fixtures\IterablePDOStatementInterface;
use Emonkak\Orm\Tests\Fixtures\Model;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Emonkak\Orm\ResultSet\FunctionResultSet
 */
class FunctionResultSetTest extends TestCase
{
    public function testGetInstantiator(): void
    {
        $stmt = $this->createMock(IterablePDOStatementInterface::class);
        $instantiator = function(array $props): Model {
            return new Model($props);
        };
        $result = new FunctionResultSet($stmt, $instantiator);
        $this->assertSame($instantiator, $result->getInstantiator());
    }

    public function testGetIterator(): void
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
        $instantiator = function(array $props): Model {
            return new Model($props);
        };
        $result = new FunctionResultSet($stmt, $instantiator);

        $expected = [
            new Model(['foo' => 1]),
            new Model(['foo' => 2]),
            new Model(['foo' => 3]),
            new Model(['foo' => 4]),
        ];

        $this->assertEquals($expected, iterator_to_array($result));
    }

    public function testToArray(): void
    {
        $stmt = $this->createMock(IterablePDOStatementInterface::class);
        $stmt
            ->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $stmt
            ->expects($this->once())
            ->method('fetchAll')
            ->with(\PDO::FETCH_ASSOC)
            ->willReturn([['foo' => 123], ['foo' => 456]]);
        $instantiator = function(array $props): Model {
            return new Model($props);
        };
        $result = new FunctionResultSet($stmt, $instantiator);

        $expectedResult = [new Model(['foo' => 123]), new Model(['foo' => 456])];

        $this->assertEquals($expectedResult, $result->toArray());
    }

    public function testFirst(): void
    {
        $stmt = $this->createMock(IterablePDOStatementInterface::class);
        $stmt
            ->expects($this->exactly(2))
            ->method('execute')
            ->willReturn(true);
        $stmt
            ->expects($this->exactly(2))
            ->method('fetch')
            ->with(\PDO::FETCH_ASSOC)
            ->willReturn(['foo' => 123, 'bar' => 345]);
        $instantiator = function(array $props): Model {
            return new Model($props);
        };
        $result = new FunctionResultSet($stmt, $instantiator);

        $expectedResult = new Model(['foo' => 123, 'bar' => 345]);

        $this->assertEquals($expectedResult, $result->first());
        $this->assertEquals($expectedResult, $result->firstOrDefault());
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
        $instantiator = function(array $props): Model {
            return new Model($props);
        };
        $result = new FunctionResultSet($stmt, $instantiator);

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
        $instantiator = function(array $props): Model {
            return new Model($props);
        };
        $result = new FunctionResultSet($stmt, $instantiator);

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
        $instantiator = function(array $props): Model {
            return new Model($props);
        };
        $result = new FunctionResultSet($stmt, $instantiator);

        $expectedResult = [
            new Model(['foo' => 1]),
            new Model(['foo' => 2]),
            new Model(['foo' => 3]),
            new Model(['foo' => 4]),
        ];
        $predicate = function(Model $value): bool {
            return $value->foo % 2 === 0;
        };

        $this->assertEquals(new Model(['foo' => 2]), $result->first($predicate));
        $this->assertEquals(new Model(['foo' => 2]), $result->firstOrDefault($predicate));
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
        $instantiator = function(array $props): Model {
            return new Model($props);
        };
        $result = new FunctionResultSet($stmt, $instantiator);
        $predicate = function(Model $value): bool {
            return false;
        };

        $result->first($predicate);
    }

    public function testFirstWithPredicateReturnsDefaultValue(): void
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
        $instantiator = function(array $props): Model {
            return new Model($props);
        };
        $result = new FunctionResultSet($stmt, $instantiator);

        $predicate = function(Model $value): bool {
            return false;
        };

        $this->assertNull($result->firstOrDefault($predicate));
    }
}
