<?php

namespace Emonkak\Orm\Tests\ResultSet;

use Emonkak\Orm\ResultSet\ArrayResultSet;
use Emonkak\Orm\Tests\Fixtures\MockedPDOStatementInterface;

/**
 * @covers Emonkak\Orm\ResultSet\ArrayResultSet
 */
class ArrayResultSetTest extends \PHPUnit_Framework_TestCase
{
    private $stmt;

    private $result;

    public function setUp()
    {
        $this->stmt = $this->createMock(MockedPDOStatementInterface::class);
        $this->result = new ArrayResultSet($this->stmt);
    }

    public function testGetClass()
    {
        $this->assertNull($this->result->getClass());
    }

    public function testGetIterator()
    {
        $expected = [['foo' => 123], ['foo' => 345]];

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
            ->willReturn(new \ArrayIterator($expected));

        $this->assertSame($expected, iterator_to_array($this->result));
    }

    public function testToArray()
    {
        $expected = [['foo' => 123], ['foo' => 345]];

        $this->stmt
            ->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $this->stmt
            ->expects($this->once())
            ->method('fetchAll')
            ->with(\PDO::FETCH_ASSOC)
            ->willReturn($expected);

        $this->assertSame($expected, $this->result->toArray());
    }

    public function testFirst()
    {
        $expected = ['foo' => 123, 'bar' => 345];

        $this->stmt
            ->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $this->stmt
            ->expects($this->once())
            ->method('fetch')
            ->with(\PDO::FETCH_ASSOC)
            ->willReturn($expected);

        $this->assertSame($expected, $this->result->first());
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
            ->method('fetch')
            ->with(\PDO::FETCH_ASSOC)
            ->willReturn(false);

        $this->result->first();
    }

    public function testFirstWithPredicate()
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
            return $value['foo'] % 2 === 0;
        };

        $this->assertEquals(['foo' => 2], $this->result->first($predicate));
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
}
