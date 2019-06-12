<?php

namespace Emonkak\Orm\Tests;

use Emonkak\Orm\DeleteBuilder;
use Emonkak\Orm\Grammar\AbstractGrammar;
use Emonkak\Orm\Grammar\GrammarInterface;
use Emonkak\Orm\InsertBuilder;
use Emonkak\Orm\SelectBuilder;
use Emonkak\Orm\Sql;
use Emonkak\Orm\UpdateBuilder;

/**
 * @covers Emonkak\Orm\Grammar\AbstractGrammar
 */
class AbstractGrammarTest extends \PHPUnit_Framework_TestCase
{
    use QueryBuilderTestTrait;

    public function testGetSelect()
    {
        $grammar = $this->getMockForAbstractClass(AbstractGrammar::class);
        $builder = $grammar->getSelect();

        $this->assertInstanceOf(SelectBuilder::class, $builder);
        $this->assertSame($grammar, $builder->getGrammar());
    }

    public function testGetInsert()
    {
        $grammar = $this->getMockForAbstractClass(AbstractGrammar::class);
        $builder = $grammar->getInsert();

        $this->assertInstanceOf(InsertBuilder::class, $builder);
        $this->assertSame($grammar, $builder->getGrammar());
    }

    public function testGetUpdate()
    {
        $grammar = $this->getMockForAbstractClass(AbstractGrammar::class);
        $builder = $grammar->getUpdate();

        $this->assertInstanceOf(UpdateBuilder::class, $builder);
        $this->assertSame($grammar, $builder->getGrammar());
    }

    public function testGetDelete()
    {
        $grammar = $this->getMockForAbstractClass(AbstractGrammar::class);
        $builder = $grammar->getDelete();

        $this->assertInstanceOf(DeleteBuilder::class, $builder);
        $this->assertSame($grammar, $builder->getGrammar());
    }

    /**
     * @dataProvider providerExpression
     */
    public function testExpression($value, $expectedSql, array $expectedBindings)
    {
        $grammar = $this->getMockForAbstractClass(AbstractGrammar::class);
        $query = $grammar->expression($value);

        $this->assertQueryIs($expectedSql, $expectedBindings, $query);
    }

    public function providerExpression()
    {
        return [
            [new Sql('?', ['foo']), '?', ['foo']],
            [$this->getSelectBuilder()->from('t1')->where('c1', '=', 'foo'), '(SELECT * FROM t1 WHERE (c1 = ?))', ['foo']],
            ['foo', 'foo', []],
        ];
    }

    /**
     * @dataProvider providerExprThrowsUnexpectedValueException
     *
     * @expectedException UnexpectedValueException
     */
    public function testExprThrowsUnexpectedValueException($value)
    {
        $grammar = $this->getMockForAbstractClass(AbstractGrammar::class);
        $grammar->expression($value);
    }

    public function providerExprThrowsUnexpectedValueException()
    {
        return [
            [123],
            [1.23],
            [true],
            [false],
            [null],
            [[1, 2, 3]],
            [new \stdClass()],
        ];
    }

    /**
     * @dataProvider providerLiteral
     */
    public function testLiteral($value, $expectedSql, array $expectedBindings)
    {
        $grammar = $this->getMockForAbstractClass(AbstractGrammar::class);
        $query = $grammar->literal($value);

        $this->assertQueryIs($expectedSql, $expectedBindings, $query);
    }

    public function providerLiteral()
    {
        return [
            [new Sql('?', ['foo']), '?', ['foo']],
            [$this->getSelectBuilder()->from('t1')->where('c1', '=', 'foo'), '(SELECT * FROM t1 WHERE (c1 = ?))', ['foo']],
            ['foo', '?', ['foo']],
            [123, '?', [123]],
            [1.23, '?', [1.23]],
            [true, '?', [true]],
            [false, '?', [false]],
            [null, 'NULL', []],
            [[1, 2, 3], '(?, ?, ?)', [1, 2, 3]],
        ];
    }

    /**
     * @dataProvider providerLiteralThrowsUnexpectedValueException
     *
     * @expectedException UnexpectedValueException
     */
    public function testLiteralThrowsUnexpectedValueException($value)
    {
        $grammar = $this->getMockForAbstractClass(AbstractGrammar::class);
        $grammar->literal($value);
    }

    public function providerLiteralThrowsUnexpectedValueException()
    {
        return [
            [new \stdClass()],
        ];
    }

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
