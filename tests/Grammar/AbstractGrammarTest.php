<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests;

use Emonkak\Orm\DeleteBuilder;
use Emonkak\Orm\Grammar\AbstractGrammar;
use Emonkak\Orm\Grammar\DefaultGrammar;
use Emonkak\Orm\Grammar\GrammarInterface;
use Emonkak\Orm\InsertBuilder;
use Emonkak\Orm\SelectBuilder;
use Emonkak\Orm\Sql;
use Emonkak\Orm\Tests\Fixtures\Id;
use Emonkak\Orm\UpdateBuilder;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Emonkak\Orm\Grammar\AbstractGrammar
 */
class AbstractGrammarTest extends TestCase
{
    use QueryBuilderTestTrait;

    public function testGetSelect(): void
    {
        /** @psalm-suppress ArgumentTypeCoercion */
        $grammar = $this->getMockBuilder(AbstractGrammar::class)
            ->onlyMethods(array_values(array_diff(get_class_methods(GrammarInterface::class), [
                'getSelectBuilder',
            ])))
            ->getMock();
        $queryBuilder = $grammar->getSelectBuilder();

        $this->assertInstanceOf(SelectBuilder::class, $queryBuilder);
        $this->assertSame($grammar, $queryBuilder->getGrammar());
    }

    public function testGetInsert(): void
    {
        /** @psalm-suppress ArgumentTypeCoercion */
        $grammar = $this->getMockBuilder(AbstractGrammar::class)
            ->onlyMethods(array_values(array_diff(get_class_methods(GrammarInterface::class), [
                'getInsertBuilder',
            ])))
            ->getMock();
        $queryBuilder = $grammar->getInsertBuilder();

        $this->assertInstanceOf(InsertBuilder::class, $queryBuilder);
        $this->assertSame($grammar, $queryBuilder->getGrammar());
    }

    public function testGetUpdate(): void
    {
        /** @psalm-suppress ArgumentTypeCoercion */
        $grammar = $this->getMockBuilder(AbstractGrammar::class)
            ->onlyMethods(array_values(array_diff(get_class_methods(GrammarInterface::class), [
                'getUpdateBuilder',
            ])))
            ->getMock();
        $queryBuilder = $grammar->getUpdateBuilder();

        $this->assertInstanceOf(UpdateBuilder::class, $queryBuilder);
        $this->assertSame($grammar, $queryBuilder->getGrammar());
    }

    public function testGetDelete(): void
    {
        /** @psalm-suppress ArgumentTypeCoercion */
        $grammar = $this->getMockBuilder(AbstractGrammar::class)
            ->onlyMethods(array_values(array_diff(get_class_methods(GrammarInterface::class), [
                'getDeleteBuilder',
            ])))
            ->getMock();
        $queryBuilder = $grammar->getDeleteBuilder();

        $this->assertInstanceOf(DeleteBuilder::class, $queryBuilder);
        $this->assertSame($grammar, $queryBuilder->getGrammar());
    }

    /**
     * @dataProvider providerLift
     */
    public function testLift(mixed $value, string $expectedSql, array $expectedBindings): void
    {
        /** @psalm-suppress ArgumentTypeCoercion */
        $grammar = $this->getMockBuilder(AbstractGrammar::class)
            ->onlyMethods(array_values(array_diff(get_class_methods(GrammarInterface::class), [
                'lvalue',
            ])))
            ->getMock();
        $query = $grammar->lvalue($value);

        $this->assertQueryIs($expectedSql, $expectedBindings, $query);
    }

    public static function providerLift(): array
    {
        return [
            [new Sql('?', ['foo']), '?', ['foo']],
            [(new SelectBuilder(new DefaultGrammar()))->from('t1')->where('c1', '=', 'foo'), '(SELECT * FROM t1 WHERE (c1 = ?))', ['foo']],
            ['foo', 'foo', []],
        ];
    }

    /**
     * @dataProvider providerLiftThrowsUnexpectedValueException
     */
    public function testLiftThrowsUnexpectedValueException(mixed $value): void
    {
        $this->expectException(\UnexpectedValueException::class);

        /** @psalm-suppress ArgumentTypeCoercion */
        $grammar = $this->getMockBuilder(AbstractGrammar::class)
            ->onlyMethods(array_values(array_diff(get_class_methods(GrammarInterface::class), [
                'lvalue',
            ])))
            ->getMock();
        $grammar->lvalue($value);
    }

    public static function providerLiftThrowsUnexpectedValueException(): array
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
     * @dataProvider providerValue
     */
    public function testValue(mixed $value, string $expectedSql, array $expectedBindings): void
    {
        /** @psalm-suppress ArgumentTypeCoercion */
        $grammar = $this->getMockBuilder(AbstractGrammar::class)
            ->onlyMethods(array_values(array_diff(get_class_methods(GrammarInterface::class), [
                'rvalue',
            ])))
            ->getMock();
        $query = $grammar->rvalue($value);

        $this->assertQueryIs($expectedSql, $expectedBindings, $query);
    }

    public static function providerValue(): array
    {
        return [
            [new Sql('?', ['foo']), '?', ['foo']],
            [(new SelectBuilder(new DefaultGrammar()))->from('t1')->where('c1', '=', 'foo'), '(SELECT * FROM t1 WHERE (c1 = ?))', ['foo']],
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
     * @dataProvider providerValueThrowsUnexpectedValueException
     */
    public function testValueThrowsUnexpectedValueException(mixed $value): void
    {
        $this->expectException(\UnexpectedValueException::class);

        /** @psalm-suppress ArgumentTypeCoercion */
        $grammar = $this->getMockBuilder(AbstractGrammar::class)
            ->onlyMethods(array_values(array_diff(get_class_methods(GrammarInterface::class), [
                'condition',
                'lvalue',
                'rvalue',
            ])))
            ->getMock();
        $grammar->rvalue($value);
    }

    public static function providerValueThrowsUnexpectedValueException(): array
    {
        return [
            [new \stdClass()],
        ];
    }

    public function testConditionWithOneArgument(): void
    {
        $expr = 'SELECT 1';
        $expectedQuery = new Sql('SELECT 1');

        /** @psalm-suppress ArgumentTypeCoercion */
        $grammar = $this->getMockBuilder(AbstractGrammar::class)
            ->onlyMethods(/** @var non-empty-string[] */ array_values(array_diff(get_class_methods(GrammarInterface::class), [
                'condition',
                'lvalue',
                'rvalue',
            ])))
            ->getMock();

        $this->assertEquals($expectedQuery, $grammar->condition($expr));
    }

    public function testConditionWithTwoArgument(): void
    {
        $operator = 'EXISTS';
        $lhsExpr = '(SELECT 1)';
        $lhs = new Sql($lhsExpr);
        $expectedQuery = new Sql('EXISTS (SELECT 1)');

        /** @psalm-suppress ArgumentTypeCoercion */
        $grammar = $this->getMockBuilder(AbstractGrammar::class)
            ->onlyMethods(array_values(array_diff(get_class_methods(GrammarInterface::class), [
                'condition',
                'lvalue',
                'rvalue',
            ])))
            ->getMock();
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

        /** @psalm-suppress ArgumentTypeCoercion */
        $grammar = $this->getMockBuilder(AbstractGrammar::class)
            ->onlyMethods(array_values(array_diff(get_class_methods(GrammarInterface::class), [
                'condition',
                'lvalue',
                'rvalue',
            ])))
            ->getMock();
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

        /** @psalm-suppress ArgumentTypeCoercion */
        $grammar = $this->getMockBuilder(AbstractGrammar::class)
            ->onlyMethods(array_values(array_diff(get_class_methods(GrammarInterface::class), [
                'condition',
                'lvalue',
                'rvalue',
            ])))
            ->getMock();
        $grammar
            ->expects($this->any())
            ->method('betweenOperator')
            ->with($operator, $lhs, $start, $end)
            ->willReturn($expectedQuery);

        $this->assertEquals($expectedQuery, $grammar->condition($lhsExpr, $operator, $startExpr, $endExpr));
    }
}
