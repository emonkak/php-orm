<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\Aggregatable;
use PHPUnit\Framework\TestCase;

/**
 * @covers Emonkak\Orm\Aggregatable
 */
class AggregatableTest extends TestCase
{
    public function testAvg()
    {
        $pdo = $this->createMock(PDOInterface::class);

        $aggregatable = $this->getMockForTrait(Aggregatable::class);
        $aggregatable
            ->expects($this->once())
            ->method('aggregate')
            ->with($this->identicalTo($pdo), 'AVG(c1)')
            ->willReturn(123);

        $this->assertSame(123, $aggregatable->avg($pdo, 'c1'));
    }

    public function testCount()
    {
        $pdo = $this->createMock(PDOInterface::class);

        $aggregatable = $this->getMockForTrait(Aggregatable::class);
        $aggregatable
            ->expects($this->once())
            ->method('aggregate')
            ->with($this->identicalTo($pdo), 'COUNT(*)')
            ->willReturn(123);

        $this->assertSame(123, $aggregatable->count($pdo, '*'));
    }

    public function testMax()
    {
        $pdo = $this->createMock(PDOInterface::class);

        $aggregatable = $this->getMockForTrait(Aggregatable::class);
        $aggregatable
            ->expects($this->once())
            ->method('aggregate')
            ->with($this->identicalTo($pdo), 'MAX(c1)')
            ->willReturn(123);

        $this->assertSame(123, $aggregatable->max($pdo, 'c1'));
    }

    public function testMin()
    {
        $pdo = $this->createMock(PDOInterface::class);

        $aggregatable = $this->getMockForTrait(Aggregatable::class);
        $aggregatable
            ->expects($this->once())
            ->method('aggregate')
            ->with($this->identicalTo($pdo), 'MIN(c1)')
            ->willReturn(123);

        $this->assertSame(123, $aggregatable->min($pdo, 'c1'));
    }

    public function testSum()
    {
        $pdo = $this->createMock(PDOInterface::class);

        $aggregatable = $this->getMockForTrait(Aggregatable::class);
        $aggregatable
            ->expects($this->once())
            ->method('aggregate')
            ->with($this->identicalTo($pdo), 'SUM(c1)')
            ->willReturn(123);

        $this->assertSame(123, $aggregatable->sum($pdo, 'c1'));
    }
}
