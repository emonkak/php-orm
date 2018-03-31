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
            ->method('liftExpr')
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
            ->method('liftLiteral')
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
            ->method('liftExpr')
            ->with($lhsExpr)
            ->willReturn($lhs);
        $grammar
            ->expects($this->once())
            ->method('liftLiteral')
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
            ->method('liftExpr')
            ->with($lhsExpr)
            ->willReturn($lhs);
        $grammar
            ->expects($this->at(1))
            ->method('liftLiteral')
            ->with($startExpr)
            ->willReturn($start);
        $grammar
            ->expects($this->at(2))
            ->method('liftLiteral')
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
     * @dataProvider providerOperators
     */
    public function testOperators($method, $operator)
    {
        $lhsExpr = 'c1';
        $lhs = new Sql('c1');
        $rhsExpr = 123;
        $rhs = new Sql('?', [123]);
        $expectedQuery = new Sql("(c1 $operator ?)", [123]);

        $grammar = $this->createMock(GrammarInterface::class);
        $grammar
            ->expects($this->once())
            ->method('liftExpr')
            ->with($lhsExpr)
            ->willReturn($lhs);
        $grammar
            ->expects($this->once())
            ->method('liftLiteral')
            ->with($rhsExpr)
            ->willReturn($rhs);
        $grammar
            ->expects($this->once())
            ->method('operator')
            ->with($operator, $lhs, $rhs)
            ->willReturn($expectedQuery);

        $conditionMaker = new ConditionMaker($grammar);

        $this->assertEquals($expectedQuery, $conditionMaker->$method($lhsExpr, $rhsExpr));
    }

    public function providerOperators()
    {
        return [
            ['equal', '='],
            ['notEqual', '<>'],
            ['lessThan', '<'],
            ['lessThanOrEqual', '<='],
            ['greaterThan', '>'],
            ['greaterThanOrEqual', '>='],
            ['in', 'IN'],
            ['notIn', 'NOT IN'],
            ['like', 'LIKE'],
            ['notLike', 'NOT LIKE'],
            ['_and', 'AND'],
            ['_or', 'OR'],
        ];
    }

    /**
     * @dataProvider providerIsNullOperators
     */
    public function testIsNullOperators($method, $operator)
    {
        $lhsExpr = 'c1';
        $lhs = new Sql('c1');
        $rhsExpr = nulL;
        $rhs = new Sql('NULL');
        $expectedQuery = new Sql("(c1 IS NULL)", []);

        $grammar = $this->createMock(GrammarInterface::class);
        $grammar
            ->expects($this->once())
            ->method('liftExpr')
            ->with($lhsExpr)
            ->willReturn($lhs);
        $grammar
            ->expects($this->once())
            ->method('liftLiteral')
            ->with($rhsExpr)
            ->willReturn($rhs);
        $grammar
            ->expects($this->once())
            ->method('operator')
            ->with($operator, $lhs, $rhs)
            ->willReturn($expectedQuery);

        $conditionMaker = new ConditionMaker($grammar);

        $this->assertEquals($expectedQuery, $conditionMaker->$method($lhsExpr));
    }

    public function providerIsNullOperators()
    {
        return [
            ['isNull', 'IS'],
            ['isNotNull', 'IS NOT'],
        ];
    }

    /**
     * @dataProvider providerUnaryOperators
     */
    public function testUnaryOperators($method ,$operator)
    {
        $rhsExpr = 'SELECT 1';
        $rhs = new Sql('(SELECT 1)');
        $expectedQuery = new Sql("$operator (SELECT 1)");

        $grammar = $this->createMock(GrammarInterface::class);
        $grammar
            ->expects($this->once())
            ->method('liftLiteral')
            ->with($rhsExpr)
            ->willReturn($rhs);
        $grammar
            ->expects($this->once())
            ->method('unaryOperator')
            ->with($operator, $rhs)
            ->willReturn($expectedQuery);

        $conditionMaker = new ConditionMaker($grammar);

        $this->assertEquals($expectedQuery, $conditionMaker->$method($rhsExpr));
    }

    public function providerUnaryOperators()
    {
        return [
            ['not', 'NOT'],
            ['exists', 'EXISTS'],
            ['notExists', 'NOT EXISTS'],
            ['all', 'ALL'],
            ['notAll', 'NOT ALL'],
            ['any', 'ANY'],
            ['notAny', 'NOT ANY'],
            ['some', 'SOME'],
            ['notSome', 'NOT SOME'],
        ];
    }

    /**
     * @dataProvider providerBetweenOperators
     */
    public function testBetweenOperators($method, $operator)
    {
        $lhsExpr = 'c1';
        $lhs = new Sql('c1');
        $startExpr = 123;
        $start = new Sql('?', [123]);
        $endExpr = 456;
        $end = new Sql('?', [456]);
        $expectedQuery = new Sql("(c1 $operator ? AND ?)", [123, 456]);

        $grammar = $this->createMock(GrammarInterface::class);
        $grammar
            ->expects($this->once())
            ->method('liftExpr')
            ->with($lhsExpr)
            ->willReturn($lhs);
        $grammar
            ->expects($this->at(1))
            ->method('liftLiteral')
            ->with($startExpr)
            ->willReturn($start);
        $grammar
            ->expects($this->at(2))
            ->method('liftLiteral')
            ->with($endExpr)
            ->willReturn($end);
        $grammar
            ->expects($this->once())
            ->method('betweenOperator')
            ->with($operator, $lhs, $start, $end)
            ->willReturn($expectedQuery);

        $conditionMaker = new ConditionMaker($grammar);

        $this->assertEquals($expectedQuery, $conditionMaker->$method($lhsExpr, $startExpr, $endExpr));
    }

    public function providerBetweenOperators()
    {
        return [
            ['between', 'BETWEEN'],
            ['notBetween', 'NOT BETWEEN']
        ];
    }

    public function testGetGrammar()
    {
        $grammar = $this->createMock(GrammarInterface::class);

        $conditionMaker = new ConditionMaker($grammar);

        $this->assertSame($grammar, $conditionMaker->getGrammar());
    }
}
