<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests;

use Emonkak\Database\PDOInterface;
use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\Explainable;
use Emonkak\Orm\Sql;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Emonkak\Orm\Explainable
 */
class ExplainableTest extends TestCase
{
    public function testExplain(): void
    {
        $explainable = $this->getMockBuilder(ExplainableMock::class)
            ->onlyMethods(['build'])
            ->getMock();
        $explainable
            ->expects($this->once())
            ->method('build')
            ->willReturn(new Sql('SELECT * FROM t1'));

        $explainResult = ['foo', 'bar'];

        $stmt = $this->createMock(PDOStatementInterface::class);
        $stmt
            ->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $stmt
            ->expects($this->once())
            ->method('fetchAll')
            ->willReturn($explainResult);

        $pdo = $this->createMock(PDOInterface::class);
        $pdo
            ->expects($this->once())
            ->method('prepare')
            ->with('EXPLAIN SELECT * FROM t1')
            ->willReturn($stmt);

        $this->assertEquals($explainResult, $explainable->explain($pdo));
    }
}

abstract class ExplainableMock
{
    use Explainable;
}
