<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests;

use Emonkak\Database\PDOInterface;
use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\Preparable;
use Emonkak\Orm\Sql;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Emonkak\Orm\Preparable
 */
class PreparableTest extends TestCase
{
    public function testPrepare(): void
    {
        $query = new Sql(
            'SELECT * FROM t1 WHERE c1 = ? AND c2 = ? AND c3 = ?',
            ['foo', 123, true, null]
        );

        $preparable = $this->getMockBuilder(PreparableMock::class)
            ->onlyMethods(['build'])
            ->getMock();
        $preparable
            ->expects($this->once())
            ->method('build')
            ->willReturn($query);

        $stmt = $this->createMock(PDOStatementInterface::class);
        $stmt
            ->expects($this->exactly(4))
            ->method('bindValue')
            ->willReturnMap([
                [1, 'foo', \PDO::PARAM_STR, true],
                [2, 123, \PDO::PARAM_INT, true],
                [3, true, \PDO::PARAM_BOOL, true],
                [4, null, \PDO::PARAM_NULL, true],
            ]);

        $pdo = $this->createMock(PDOInterface::class);
        $pdo
            ->expects($this->once())
            ->method('prepare')
            ->with($query->getSql())
            ->willReturn($stmt);

        $this->assertSame($stmt, $preparable->prepare($pdo));
    }

    public function testPrepareThrowsRuntimeException(): void
    {
        $this->expectException(\RuntimeException::class);

        $query = new Sql('SELECT 1', []);

        $preparable = $this->getMockBuilder(PreparableMock::class)
            ->onlyMethods(['build'])
            ->getMock();
        $preparable
            ->expects($this->once())
            ->method('build')
            ->willReturn($query);

        $pdo = $this->createMock(PDOInterface::class);
        $pdo
            ->expects($this->once())
            ->method('prepare')
            ->with($query->getSql())
            ->willReturn(false);

        $preparable->prepare($pdo);
    }

    public function testPrepareThrowsUnexpectedValueException(): void
    {
        $this->expectException(\UnexpectedValueException::class);

        $query = new Sql(
            'SELECT * FROM t1 WHERE c1 = ?',
            [new \stdClass()]
        );

        $preparable = $this->getMockBuilder(PreparableMock::class)
            ->onlyMethods(['build'])
            ->getMock();
        $preparable
            ->expects($this->once())
            ->method('build')
            ->willReturn($query);

        $stmt = $this->createMock(PDOStatementInterface::class);

        $pdo = $this->createMock(PDOInterface::class);
        $pdo
            ->expects($this->once())
            ->method('prepare')
            ->with($query->getSql())
            ->willReturn($stmt);

        $this->assertSame($stmt, $preparable->prepare($pdo));
    }

    public function testExecute(): void
    {
        $query = new Sql(
            'SELECT * FROM t1 WHERE c1 = ? AND c2 = ? AND c3 = ?',
            ['foo', 123, true, null]
        );

        $preparable = $this->getMockBuilder(PreparableMock::class)
            ->onlyMethods(['build'])
            ->getMock();
        $preparable
            ->expects($this->once())
            ->method('build')
            ->willReturn($query);

        $stmt = $this->createMock(PDOStatementInterface::class);
        $stmt
            ->expects($this->exactly(4))
            ->method('bindValue')
            ->willReturnMap([
                [1, 'foo', \PDO::PARAM_STR, true],
                [2, 123, \PDO::PARAM_INT, true],
                [3, true, \PDO::PARAM_BOOL, true],
                [4, null, \PDO::PARAM_NULL, true],
            ]);
        $stmt
            ->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $pdo = $this->createMock(PDOInterface::class);
        $pdo
            ->expects($this->once())
            ->method('prepare')
            ->with($query->getSql())
            ->willReturn($stmt);

        $this->assertSame(true, $preparable->execute($pdo));
    }
}

abstract class PreparableMock
{
    use Preparable;
}
