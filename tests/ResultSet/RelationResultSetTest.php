<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\ResultSet;

use Emonkak\Orm\Relation\RelationInterface;
use Emonkak\Orm\ResultSet\RelationResultSet;
use Emonkak\Orm\ResultSet\ResultSetInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers Emonkak\Orm\ResultSet\RelationResultSet
 */
class RelationResultSetTest extends TestCase
{
    private $outerResult;

    private $relation;

    private $result;

    public function setUp(): void
    {
        $this->outerResult = $this->createMock(ResultSetInterface::class);
        $this->relation = $this->createMock(RelationInterface::class);
        $this->result = new RelationResultSet($this->outerResult, \stdClass::class, $this->relation);
    }

    public function testGetOuterResult(): void
    {
        $this->assertSame($this->outerResult, $this->result->getOuterResult());
    }

    public function testGetOuterClass(): void
    {
        $this->assertSame(\stdClass::class, $this->result->getOuterClass());
    }

    public function testGetIterator(): void
    {
        $expected = new \ArrayIterator([
            ['foo' => 123],
            ['foo' => 456],
        ]);

        $this->relation
            ->expects($this->once())
            ->method('associate')
            ->with($this->identicalTo($this->outerResult))
            ->willReturn($expected);

        $this->assertSame($expected, $this->result->getIterator());
    }
}
