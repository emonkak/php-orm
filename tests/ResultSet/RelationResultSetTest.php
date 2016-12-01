<?php

namespace Emonkak\Orm\Tests\ResultSet;

use Emonkak\Orm\Relation\RelationInterface;
use Emonkak\Orm\ResultSet\RelationResultSet;
use Emonkak\Orm\ResultSet\ResultSetInterface;

/**
 * @covers Emonkak\Orm\ResultSet\RelationResultSet
 */
class RelationResultSetTest extends \PHPUnit_Framework_TestCase
{
    private $innerResult;

    private $relation;

    private $result;

    public function setUp()
    {
        $this->innerResult = $this->createMock(ResultSetInterface::class);
        $this->relation = $this->createMock(RelationInterface::class);
        $this->result = new RelationResultSet($this->innerResult, $this->relation);
    }

    public function testGetClass()
    {
        $this->innerResult
            ->expects($this->once())
            ->method('getClass')
            ->willReturn(\stdClass::class);

        $this->assertSame(\stdClass::class, $this->result->getClass());
    }

    public function testGetIterator()
    {
        $expected = new \ArrayIterator([
            ['foo' => 123],
            ['foo' => 456],
        ]);

        $this->relation
            ->expects($this->once())
            ->method('associate')
            ->with($this->identicalTo($this->innerResult))
            ->willReturn($expected);

        $this->assertSame($expected, $this->result->getIterator());
    }
}
