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

    public function setUp()
    {
        $this->stmt = $this->createMock(IterablePDOStatementInterface::class);
        $this->result = new ObjectResultSet($this->stmt, \stdClass::class, ['foo']);
    }

    public function testGetClass()
    {
        $this->assertSame(\stdClass::class, $this->result->getClass());
    }

    public function testGetIterator()
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

    public function testToArray()
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

    public function testFirst()
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

    /**
     * @expectedException RuntimeException
     */
    public function testFirstThrowsRuntimeException()
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

        $this->result->first();
    }

    public function testFirstOrDefaultReturnsDefaultValue()
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

    public function testFirstWithPredicate()
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

    /**
     * @expectedException RuntimeException
     */
    public function testFirstWithPredicateThrowsRuntimeException()
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

        $this->result->first($predicate);
    }

    public function testFirstOrDefaultWithPredicateReturnsDefaultValue()
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
