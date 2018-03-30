<?php

namespace Emonkak\Orm\Tests\Grammar;

use Emonkak\Orm\Grammar\AbstractGrammar;
use Emonkak\Orm\Sql;

/**
 * @covers Emonkak\Orm\Grammar\AbstractGrammar
 */
class AbstractGrammarTest extends \PHPUnit_Framework_TestCase
{
    public function testConditionWithOneArgument()
    {
        $expr = 'SELECT 1';
        $expectedQuery = new Sql('SELECT 1');

        $grammar = $this->getMockForAbstractClass(AbstractGrammar::class);
        $grammar
            ->expects($this->once())
            ->method('lift')
            ->with($expr)
            ->willReturn($expectedQuery);

        $this->assertSame($expectedQuery, $grammar->condition($expr));
    }

    public function testConditionWithTwoArgument()
    {
        $operator = 'EXISTS';
        $lhsExpr = '(SELECT 1)';
        $lhs = new Sql($lhsExpr);
        $expectedQuery = new Sql('EXISTS (SELECT 1)');

        $grammar = $this->getMockForAbstractClass(AbstractGrammar::class);
        $grammar
            ->expects($this->once())
            ->method('liftValue')
            ->with($lhsExpr)
            ->willReturn($lhs);
        $grammar
            ->expects($this->once())
            ->method('unaryOperator')
            ->with($operator, $lhs)
            ->willReturn($expectedQuery);

        $this->assertSame($expectedQuery, $grammar->condition($operator, $lhsExpr));
    }

    public function testConditionWithThreeArgument()
    {
        $lhsExpr = 'c1';
        $lhs = new Sql($lhsExpr);
        $operator = '=';
        $rhsExpr = 123;
        $rhs = new Sql('?', [$rhsExpr]);
        $expectedQuery = new Sql('c1 = ?', [123]);

        $grammar = $this->getMockForAbstractClass(AbstractGrammar::class);
        $grammar
            ->expects($this->once())
            ->method('lift')
            ->with($lhsExpr)
            ->willReturn($lhs);
        $grammar
            ->expects($this->once())
            ->method('liftValue')
            ->with($rhsExpr)
            ->willReturn($rhs);
        $grammar
            ->expects($this->once())
            ->method('operator')
            ->with($lhs, $operator, $rhs)
            ->willReturn($expectedQuery);

        $this->assertSame($expectedQuery, $grammar->condition($lhsExpr, $operator, $rhsExpr));
    }

    public function testConditionWithFourArgument()
    {
        $lhsExpr = 'c1';
        $lhs = new Sql($lhsExpr);
        $operator = '=';
        $startExpr = 123;
        $start = new Sql('?', [$startExpr]);
        $endExpr = 456;
        $end = new Sql('?', [$endExpr]);
        $expectedQuery = new Sql('c1 BETWEEN ? AND ?', [123, 456]);

        $grammar = $this->getMockForAbstractClass(AbstractGrammar::class);
        $grammar
            ->expects($this->once())
            ->method('lift')
            ->with($lhsExpr)
            ->willReturn($lhs);
        $grammar
            ->expects($this->at(1))
            ->method('liftValue')
            ->with($startExpr)
            ->willReturn($start);
        $grammar
            ->expects($this->at(2))
            ->method('liftValue')
            ->with($endExpr)
            ->willReturn($end);
        $grammar
            ->expects($this->once())
            ->method('betweenOperator')
            ->with($lhs, $operator, $start, $end)
            ->willReturn($expectedQuery);

        $this->assertSame($expectedQuery, $grammar->condition($lhsExpr, $operator, $startExpr, $endExpr));
    }
}
