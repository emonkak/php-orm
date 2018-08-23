<?php

namespace Emonkak\Orm\Tests;

use Emonkak\Orm\Grammar\AbstractGrammar;
use Emonkak\Orm\Grammar\GrammarInterface;
use Emonkak\Orm\Sql;

/**
 * @covers Emonkak\Orm\Grammar\AbstractGrammar
 */
class AbstractGrammarTest extends \PHPUnit_Framework_TestCase
{
    use QueryBuilderTestTrait;

    public function testConditionWithOneArgument()
    {
        $expr = 'SELECT 1';
        $expectedQuery = new Sql('SELECT 1');

        $grammar = $this->getMockForAbstractClass(AbstractGrammar::class);

        $this->assertEquals($expectedQuery, $grammar->condition($expr));
    }

    public function testConditionWithTwoArgument()
    {
        $operator = 'EXISTS';
        $lhsExpr = '(SELECT 1)';
        $lhs = new Sql($lhsExpr);
        $expectedQuery = new Sql('EXISTS (SELECT 1)');

        $grammar = $this->getMockForAbstractClass(AbstractGrammar::class);
        $grammar
            ->expects($this->any())
            ->method('unaryOperator')
            ->with($operator, $lhs)
            ->willReturn($expectedQuery);

        $this->assertEquals($expectedQuery, $grammar->condition($operator, $lhsExpr));
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
            ->expects($this->any())
            ->method('operator')
            ->with($operator, $lhs, $rhs)
            ->willReturn($expectedQuery);

        $this->assertEquals($expectedQuery, $grammar->condition($lhsExpr, $operator, $rhsExpr));
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
            ->expects($this->any())
            ->method('betweenOperator')
            ->with($operator, $lhs, $start, $end)
            ->willReturn($expectedQuery);

        $this->assertEquals($expectedQuery, $grammar->condition($lhsExpr, $operator, $startExpr, $endExpr));
    }
}