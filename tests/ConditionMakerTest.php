<?php

namespace Emonkak\Orm\Tests;

use Emonkak\Orm\ConditionMaker;
use Emonkak\Orm\Grammar\GrammarInterface;
use Emonkak\Orm\Sql;

/**
 * @covers Emonkak\Orm\ConditionMaker
 */
class ConditionMakerTest extends \PHPUnit_Framework_TestCase
{
    use QueryBuilderTestTrait;

    public function testInvokeWithOneArgument()
    {
        $expr = 'SELECT 1';
        $expectedQuery = new Sql('SELECT 1');

        $grammar = $this->createMock(GrammarInterface::class);
        $grammar
            ->expects($this->once())
            ->method('lift')
            ->with($expr)
            ->willReturn($expectedQuery);

        $conditionMaker = new ConditionMaker($grammar);

        $this->assertEquals($expectedQuery, $conditionMaker($expr));
    }

    public function testInvokeWithTwoArgument()
    {
        $operator = 'EXISTS';
        $lhsExpr = '(SELECT 1)';
        $lhs = new Sql($lhsExpr);
        $expectedQuery = new Sql('EXISTS (SELECT 1)');

        $grammar = $this->createMock(GrammarInterface::class);
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

        $conditionMaker = new ConditionMaker($grammar);

        $this->assertEquals($expectedQuery, $conditionMaker($operator, $lhsExpr));
    }

    public function testInvokeWithThreeArgument()
    {
        $lhsExpr = 'c1';
        $lhs = new Sql($lhsExpr);
        $operator = '=';
        $rhsExpr = 123;
        $rhs = new Sql('?', [$rhsExpr]);
        $expectedQuery = new Sql('c1 = ?', [123]);

        $grammar = $this->createMock(GrammarInterface::class);
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
            ->with($operator, $lhs, $rhs)
            ->willReturn($expectedQuery);

        $conditionMaker = new ConditionMaker($grammar);

        $this->assertEquals($expectedQuery, $conditionMaker($lhsExpr, $operator, $rhsExpr));
    }

    public function testInvokeWithFourArgument()
    {
        $lhsExpr = 'c1';
        $lhs = new Sql($lhsExpr);
        $operator = '=';
        $startExpr = 123;
        $start = new Sql('?', [$startExpr]);
        $endExpr = 456;
        $end = new Sql('?', [$endExpr]);
        $expectedQuery = new Sql('c1 BETWEEN ? AND ?', [123, 456]);

        $grammar = $this->createMock(GrammarInterface::class);
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
            ->with($operator, $lhs, $start, $end)
            ->willReturn($expectedQuery);

        $conditionMaker = new ConditionMaker($grammar);

        $this->assertEquals($expectedQuery, $conditionMaker($lhsExpr, $operator, $startExpr, $endExpr));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCallWithoutArgumentThrowsException()
    {
        $grammar = $this->createMock(GrammarInterface::class);

        $conditionMaker = new ConditionMaker($grammar);

        $conditionMaker->exists();
    }

    public function testCallWithOneArgument()
    {
        $rhsExpr = 'SELECT 1';
        $rhs = new Sql('(SELECT 1)');
        $expectedQuery = new Sql('EXISTS (SELECT 1)');

        $grammar = $this->createMock(GrammarInterface::class);
        $grammar
            ->expects($this->once())
            ->method('liftValue')
            ->with($rhsExpr)
            ->willReturn($rhs);
        $grammar
            ->expects($this->once())
            ->method('unaryOperator')
            ->with('EXISTS', $rhs)
            ->willReturn($expectedQuery);

        $conditionMaker = new ConditionMaker($grammar);

        $this->assertEquals($expectedQuery, $conditionMaker->exists($rhsExpr));
    }

    public function testCallWithTwoArgument()
    {
        $lhsExpr = 'c1';
        $lhs = new Sql('(SELECT 1)');
        $rhsExpr = 123;
        $rhs = new Sql('?', [123]);
        $expectedQuery = new Sql('(c1 = ?)', [123]);

        $grammar = $this->createMock(GrammarInterface::class);
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
            ->with('=', $lhs, $rhs)
            ->willReturn($expectedQuery);

        $conditionMaker = new ConditionMaker($grammar);

        $this->assertEquals($expectedQuery, $conditionMaker->{'='}($lhsExpr, $rhsExpr));
    }

    public function testCallWithThreeArgument()
    {
        $lhsExpr = 'c1';
        $lhs = new Sql('(SELECT 1)');
        $startExpr = 123;
        $start = new Sql('?', [123]);
        $endExpr = 456;
        $end = new Sql('?', [456]);
        $expectedQuery = new Sql('(c1 BETWEEN ? AND ?)', [123, 456]);

        $grammar = $this->createMock(GrammarInterface::class);
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
            ->with('BETWEEN', $lhs, $start, $end)
            ->willReturn($expectedQuery);

        $conditionMaker = new ConditionMaker($grammar);

        $this->assertEquals($expectedQuery, $conditionMaker->between($lhsExpr, $startExpr, $endExpr));
    }

    public function testGetGrammar()
    {
        $grammar = $this->createMock(GrammarInterface::class);

        $conditionMaker = new ConditionMaker($grammar);

        $this->assertSame($grammar, $conditionMaker->getGrammar());
    }
}
