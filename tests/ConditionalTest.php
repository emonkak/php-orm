<?php

namespace Emonkak\Orm\Tests;

use Emonkak\Orm\Conditional;
use Emonkak\Orm\Grammar\GrammarInterface;
use Emonkak\Orm\Sql;

/**
 * @covers Emonkak\Orm\Conditional
 */
class ConditionalTest extends \PHPUnit_Framework_TestCase
{
    use QueryBuilderTestTrait;

    public function testConditionWithOneArgument()
    {
        $expr = 'SELECT 1';
        $expectedQuery = new Sql('SELECT 1');

        $grammar = $this->createMock(GrammarInterface::class);

        $conditional = new ConditionalImpl($grammar);

        $this->assertEquals($expectedQuery, $conditional->condition($expr));
    }

    public function testConditionWithTwoArgument()
    {
        $operator = 'EXISTS';
        $lhsExpr = '(SELECT 1)';
        $lhs = new Sql($lhsExpr);
        $expectedQuery = new Sql('EXISTS (SELECT 1)');

        $grammar = $this->createMock(GrammarInterface::class);
        $grammar
            ->expects($this->any())
            ->method('unaryOperator')
            ->with($operator, $lhs)
            ->willReturn($expectedQuery);

        $conditional = new ConditionalImpl($grammar);

        $this->assertEquals($expectedQuery, $conditional->condition($operator, $lhsExpr));
    }

    public function testConditionWithThreeArgument()
    {
        $lhsExpr = 'c1';
        $lhs = new Sql($lhsExpr);
        $operator = '=';
        $rhsExpr = 123;
        $rhs = new Sql('?', [$rhsExpr]);
        $expectedQuery = new Sql('c1 = ?', [123]);

        $grammar = $this->createMock(GrammarInterface::class);
        $grammar
            ->expects($this->any())
            ->method('operator')
            ->with($operator, $lhs, $rhs)
            ->willReturn($expectedQuery);

        $conditional = new ConditionalImpl($grammar);

        $this->assertEquals($expectedQuery, $conditional->condition($lhsExpr, $operator, $rhsExpr));
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

        $grammar = $this->createMock(GrammarInterface::class);
        $grammar
            ->expects($this->any())
            ->method('betweenOperator')
            ->with($operator, $lhs, $start, $end)
            ->willReturn($expectedQuery);

        $conditional = new ConditionalImpl($grammar);

        $this->assertEquals($expectedQuery, $conditional->condition($lhsExpr, $operator, $startExpr, $endExpr));
    }
}

class ConditionalImpl
{
    use Conditional;

    public function __construct(GrammarInterface $grammar)
    {
        $this->grammar = $grammar;
    }

    public function getGrammar()
    {
        return $this->grammar;
    }
}
