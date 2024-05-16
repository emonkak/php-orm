<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\ResultSet;

use Emonkak\Orm\Relation\RelationInterface;
use Emonkak\Orm\ResultSet\RelationResultSet;
use Emonkak\Orm\ResultSet\ResultSetInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Emonkak\Orm\ResultSet\RelationResultSet
 */
class RelationResultSetTest extends TestCase
{
    public function testGetOuterResult(): void
    {
        $outerResult = $this->createMock(ResultSetInterface::class);
        $relation = $this->createMock(RelationInterface::class);
        $result = new RelationResultSet($outerResult, \stdClass::class, $relation);
        $this->assertSame($outerResult, $result->getOuterResult());
    }

    public function testGetOuterClass(): void
    {
        $outerResult = $this->createMock(ResultSetInterface::class);
        $relation = $this->createMock(RelationInterface::class);
        $result = new RelationResultSet($outerResult, \stdClass::class, $relation);
        $this->assertSame(\stdClass::class, $result->getOuterClass());
    }

    public function testGetIterator(): void
    {
        $expectedResult = new \ArrayIterator([
            ['foo' => 123],
            ['foo' => 456],
        ]);

        $outerResult = $this->createMock(ResultSetInterface::class);
        $relation = $this->createMock(RelationInterface::class);
        $relation
            ->expects($this->once())
            ->method('associate')
            ->with($this->identicalTo($outerResult))
            ->willReturn($expectedResult);
        $result = new RelationResultSet($outerResult, \stdClass::class, $relation);

        $this->assertSame($expectedResult, $result->getIterator());
    }
}
