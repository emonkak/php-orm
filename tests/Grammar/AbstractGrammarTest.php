<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests;

use Emonkak\Orm\DeleteBuilder;
use Emonkak\Orm\Grammar\AbstractGrammar;
use Emonkak\Orm\InsertBuilder;
use Emonkak\Orm\SelectBuilder;
use Emonkak\Orm\Sql;
use Emonkak\Orm\UpdateBuilder;
use Emonkak\Orm\Tests\Fixtures\Id;
use PHPUnit\Framework\TestCase;

/**
 * @covers Emonkak\Orm\Grammar\AbstractGrammar
 */
class AbstractGrammarTest extends TestCase
{
    use QueryBuilderTestTrait;

    public function testGetSelect(): void
    {
        $grammar = $this->getMockForAbstractClass(AbstractGrammar::class);
        $queryBuilder = $grammar->getSelectBuilder();

        $this->assertInstanceOf(SelectBuilder::class, $queryBuilder);
        $this->assertSame($grammar, $queryBuilder->getGrammar());
    }

    public function testGetInsert(): void
    {
        $grammar = $this->getMockForAbstractClass(AbstractGrammar::class);
        $queryBuilder = $grammar->getInsertBuilder();

        $this->assertInstanceOf(InsertBuilder::class, $queryBuilder);
        $this->assertSame($grammar, $queryBuilder->getGrammar());
    }

    public function testGetUpdate(): void
    {
        $grammar = $this->getMockForAbstractClass(AbstractGrammar::class);
        $queryBuilder = $grammar->getUpdateBuilder();

        $this->assertInstanceOf(UpdateBuilder::class, $queryBuilder);
        $this->assertSame($grammar, $queryBuilder->getGrammar());
    }

    public function testGetDelete(): void
    {
        $grammar = $this->getMockForAbstractClass(AbstractGrammar::class);
        $queryBuilder = $grammar->getDeleteBuilder();

        $this->assertInstanceOf(DeleteBuilder::class, $queryBuilder);
        $this->assertSame($grammar, $queryBuilder->getGrammar());
    }

    /**
     * @dataProvider providerLift
     */
    public function testLift($value, $expectedSql, array $expectedBindings): void
    {
        $grammar = $this->getMockForAbstractClass(AbstractGrammar::class);
        $query = $grammar->lift($value);

        $this->assertQueryIs($expectedSql, $expectedBindings, $query);
    }

    public function providerLift()
    {
        return [
            [new Sql('?', ['foo']), '?', ['foo']],
            [$this->getSelectBuilder()->from('t1')->where('c1', '=', 'foo'), '(SELECT * FROM t1 WHERE (c1 = ?))', ['foo']],
            ['foo', 'foo', []],
        ];
    }

    /**
     * @dataProvider providerExprThrowsUnexpectedValueException
     */
    public function testExprThrowsUnexpectedValueException($value): void
    {
        $this->expectException(\UnexpectedValueException::class);

        $grammar = $this->getMockForAbstractClass(AbstractGrammar::class);
        $grammar->lift($value);
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
    public function testLiteral($value, $expectedSql, array $expectedBindings): void
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
            [new Id(123), '?', ['123']],
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
     */
    public function testLiteralThrowsUnexpectedValueException($value): void
    {
        $this->expectException(\UnexpectedValueException::class);

        $grammar = $this->getMockForAbstractClass(AbstractGrammar::class);
        $grammar->literal($value);
    }

    public function providerLiteralThrowsUnexpectedValueException()
    {
        return [
            [new \stdClass()],
        ];
    }

    public function testConditionWithOneArgument(): void
    {
        $expr = 'SELECT 1';
        $expectedQuery = new Sql('SELECT 1');

        $grammar = $this->getMockForAbstractClass(AbstractGrammar::class);

        $this->assertEquals($expectedQuery, $grammar->condition($expr));
    }

    public function testConditionWithTwoArgument(): void
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

    public function testConditionWithThreeArgument(): void
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

    public function testConditionWithFourArgument(): void
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
