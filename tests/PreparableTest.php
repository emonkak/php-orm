<?php

namespace Emonkak\Orm\Tests;

use Emonkak\Database\PDOInterface;
use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\Preparable;
use Emonkak\Orm\Sql;

/**
 * @covers Emonkak\Orm\Preparable
 */
class PreparableTest extends \PHPUnit_Framework_TestCase
{
    public function testPrepare()
    {
        $query = new Sql(
            'SELECT * FROM t1 WHERE c1 = ? AND c2 = ? AND c3 = ?',
            ['foo', 123, true, null]
        );

        $preparable = $this->getMockForTrait(Preparable::class);
        $preparable
            ->expects($this->once())
            ->method('build')
            ->willReturn($query);

        $stmt = $this->getMock(PDOStatementInterface::class);
        $stmt
            ->expects($this->exactly(4))
            ->method('bindValue')
            ->withConsecutive(
                [1, 'foo', \PDO::PARAM_STR],
                [2, 123, \PDO::PARAM_INT],
                [3, true, \PDO::PARAM_BOOL],
                [4, null, \PDO::PARAM_NULL]
            )
            ->willReturn(true);

        $pdo = $this->getMock(PDOInterface::class);
        $pdo
            ->expects($this->once())
            ->method('prepare')
            ->with($query->getSql())
            ->willReturn($stmt);

        $this->assertSame($stmt, $preparable->prepare($pdo));
    }

    /**
     * @expectedException UnexpectedValueException
     */
    public function testPrepareThrowsUnexpectedValueException()
    {
        $query = new Sql(
            'SELECT * FROM t1 WHERE c1 = ?',
            [new \stdClass()]
        );

        $preparable = $this->getMockForTrait(Preparable::class);
        $preparable
            ->expects($this->once())
            ->method('build')
            ->willReturn($query);

        $stmt = $this->getMock(PDOStatementInterface::class);

        $pdo = $this->getMock(PDOInterface::class);
        $pdo
            ->expects($this->once())
            ->method('prepare')
            ->with($query->getSql())
            ->willReturn($stmt);

        $this->assertSame($stmt, $preparable->prepare($pdo));
    }

    public function testExecute()
    {
        $query = new Sql(
            'SELECT * FROM t1 WHERE c1 = ? AND c2 = ? AND c3 = ?',
            ['foo', 123, true, null]
        );

        $preparable = $this->getMockForTrait(Preparable::class);
        $preparable
            ->expects($this->once())
            ->method('build')
            ->willReturn($query);

        $stmt = $this->getMock(PDOStatementInterface::class);
        $stmt
            ->expects($this->exactly(4))
            ->method('bindValue')
            ->withConsecutive(
                [1, 'foo', \PDO::PARAM_STR],
                [2, 123, \PDO::PARAM_INT],
                [3, true, \PDO::PARAM_BOOL],
                [4, null, \PDO::PARAM_NULL]
            )
            ->willReturn(true);
        $stmt
            ->expects($this->once())
            ->method('execute')
            ->willReturn(true);

        $pdo = $this->getMock(PDOInterface::class);
        $pdo
            ->expects($this->once())
            ->method('prepare')
            ->with($query->getSql())
            ->willReturn($stmt);

        $this->assertSame($stmt, $preparable->execute($pdo));
    }
}
