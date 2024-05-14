<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\Grammar;

use Emonkak\Orm\Grammar\DefaultGrammar;
use Emonkak\Orm\Sql;
use Emonkak\Orm\Tests\QueryBuilderTestTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Emonkak\Orm\Grammar\DefaultGrammar
 */
class DefaultGrammarTest extends TestCase
{
    use QueryBuilderTestTrait;

    /**
     * @var DefaultGrammar
     */
    private $grammar;

    public function setUp(): void
    {
        $this->grammar = new DefaultGrammar();
    }

    /**
     * @dataProvider providerOperator
     */
    public function testOperator(string $operator, string $lhsSql, array $lhsBindings, string $rhsSql, array $rhsBindings, string $expectedSql, array $expectedBindings): void
    {
        $lhs = new Sql($lhsSql, $lhsBindings);
        $rhs = new Sql($rhsSql, $rhsBindings);

        $query = $this->grammar->operator($operator, $lhs, $rhs);

        $this->assertQueryIs(
            $expectedSql,
            $expectedBindings,
            $query
        );
    }

    public static function providerOperator(): array
    {
        return [
            ['=', 'c1', [], '?', ['foo'], '(c1 = ?)', ['foo']],
            ['!=', 'c1', [], '?', ['foo'], '(c1 != ?)', ['foo']],
            ['<>', 'c1', [], '?', ['foo'], '(c1 <> ?)', ['foo']],
            ['<', 'c1', [], '?', ['foo'], '(c1 < ?)', ['foo']],
            ['<=', 'c1', [], '?', ['foo'], '(c1 <= ?)', ['foo']],
            ['>', 'c1', [], '?', ['foo'], '(c1 > ?)', ['foo']],
            ['>=', 'c1', [], '?', ['foo'], '(c1 >= ?)', ['foo']],
            ['IS', 'c1', [], 'NULL', [], '(c1 IS NULL)', []],
            ['IS NOT', 'c1', [], 'NULL', [], '(c1 IS NOT NULL)', []],
            ['IN', 'c1', [], '(?, ?, ?)', ['foo', 'bar', 'baz'], '(c1 IN (?, ?, ?))', ['foo', 'bar', 'baz']],
            ['NOT IN', 'c1', [], '(?, ?, ?)', ['foo', 'bar', 'baz'], '(c1 NOT IN (?, ?, ?))', ['foo', 'bar', 'baz']],
            ['LIKE', 'c1', [], '?', ['foo'], '(c1 LIKE ?)', ['foo']],
            ['NOT LIKE', 'c1', [], '?', ['foo'], '(c1 NOT LIKE ?)', ['foo']],
            ['AND', '(c1 = ?)', ['foo'], '(c2 = ?)', ['bar'], '((c1 = ?) AND (c2 = ?))', ['foo', 'bar']],
            ['OR', '(c1 = ?)', ['foo'], '(c2 = ?)', ['bar'], '((c1 = ?) OR (c2 = ?))', ['foo', 'bar']],
        ];
    }

    public function testInvalidOperatorThrowsException(): void
    {
        $this->expectException(\UnexpectedValueException::class);

        $this->grammar->operator('==', new Sql('c1'), new Sql('?', [123]));
    }

    /**
     * @dataProvider providerUnaryOperator
     */
    public function testUnaryOperator(string $operator, string $lhsSql, array $lhsBindings, string $expectedSql, array $expectedBindings): void
    {
        $lhs = new Sql($lhsSql, $lhsBindings);

        $query = $this->grammar->unaryOperator($operator, $lhs);

        $this->assertQueryIs(
            $expectedSql,
            $expectedBindings,
            $query
        );
    }

    public static function providerUnaryOperator(): array
    {
        return [
            ['NOT', 'c1', [], '(NOT c1)', []],
            ['EXISTS', '(SELECT * FROM t1 WHERE c1 = ?)', ['foo'], '(EXISTS (SELECT * FROM t1 WHERE c1 = ?))', ['foo']],
            ['NOT EXISTS', '(SELECT * FROM t1 WHERE c1 = ?)', ['foo'], '(NOT EXISTS (SELECT * FROM t1 WHERE c1 = ?))', ['foo']],
            ['ALL', '(SELECT * FROM t1 WHERE c1 = ?)', ['foo'], '(ALL (SELECT * FROM t1 WHERE c1 = ?))', ['foo']],
            ['NOT ALL', '(SELECT * FROM t1 WHERE c1 = ?)', ['foo'], '(NOT ALL (SELECT * FROM t1 WHERE c1 = ?))', ['foo']],
            ['ANY', '(SELECT * FROM t1 WHERE c1 = ?)', ['foo'], '(ANY (SELECT * FROM t1 WHERE c1 = ?))', ['foo']],
            ['NOT ANY', '(SELECT * FROM t1 WHERE c1 = ?)', ['foo'], '(NOT ANY (SELECT * FROM t1 WHERE c1 = ?))', ['foo']],
            ['SOME', '(SELECT * FROM t1 WHERE c1 = ?)', ['foo'], '(SOME (SELECT * FROM t1 WHERE c1 = ?))', ['foo']],
            ['NOT SOME', '(SELECT * FROM t1 WHERE c1 = ?)', ['foo'], '(NOT SOME (SELECT * FROM t1 WHERE c1 = ?))', ['foo']],
        ];
    }

    public function testInvalidUnaryOperatorThrowsException(): void
    {
        $this->expectException(\UnexpectedValueException::class);

        $this->grammar->unaryOperator('AND', new Sql('c1'));
    }

    /**
     * @dataProvider providerBetweenOperator
     */
    public function testBetweenOperator(string $operator, string $lhsSql, array $lhsBindings, string $startSql, array $startBindings, string $endSql, array $endBindings, string $expectedSql, array $expectedBindings): void
    {
        $lhs = new Sql($lhsSql, $lhsBindings);
        $start = new Sql($startSql, $startBindings);
        $end = new Sql($endSql, $endBindings);

        $query = $this->grammar->betweenOperator($operator, $lhs, $start, $end);

        $this->assertQueryIs(
            $expectedSql,
            $expectedBindings,
            $query
        );
    }

    public static function providerBetweenOperator(): array
    {
        return [
            ['BETWEEN', 'c1', [], '?', [123], '?', [456], '(c1 BETWEEN ? AND ?)', [123, 456]],
            ['NOT BETWEEN', 'c1', [], '?', [123], '?', [456], '(c1 NOT BETWEEN ? AND ?)', [123, 456]],
        ];
    }

    public function testInvalidBetweenOperatorThrowsException(): void
    {
        $this->expectException(\UnexpectedValueException::class);

        $this->grammar->betweenOperator('=', new Sql('c1'), new Sql('?', [123]), new Sql('?', [456]));
    }

    public function testJoin(): void
    {
        $query = $this->grammar->join(new Sql('t2'), new Sql('t1.c1 = t2.c1 AND t2.c2 = ?', ['foo']), 'LEFT INNER JOIN');
        $this->assertQueryIs(
            'LEFT INNER JOIN t2 ON t1.c1 = t2.c1 AND t2.c2 = ?',
            ['foo'],
            $query
        );

        $query = $this->grammar->join(new Sql('t2'), null, 'CROSS JOIN');
        $this->assertQueryIs(
            'CROSS JOIN t2',
            [],
            $query
        );
    }

    public function testWindow(): void
    {
        $query = $this->grammar->window('w', new Sql(''));
        $this->assertQueryIs('w AS ()', [], $query);

        $query = $this->grammar->window('w', new Sql('PARTITION BY c1'));
        $this->assertQueryIs('w AS (PARTITION BY c1)', [], $query);
    }

    public function testOrdering(): void
    {
        $query = $this->grammar->ordering(new Sql('c1 + ?', [1]), 'ASC');
        $this->assertQueryIs('c1 + ? ASC', [1], $query);

        $query = $this->grammar->ordering(new Sql('c1 + ?', [1]), 'DESC');
        $this->assertQueryIs('c1 + ? DESC', [1], $query);
    }

    public function testOrderingThrowsException(): void
    {
        $this->expectException(\UnexpectedValueException::class);

        $this->grammar->ordering(new Sql('c1 + ?', [1]), '');
    }

    public function testUnion(): void
    {
        $query = $this->grammar->union(new Sql('SELECT * FROM t1 WHERE c1 = ?', ['foo']), 'UNION ALL');
        $this->assertQueryIs(
            'UNION ALL SELECT * FROM t1 WHERE c1 = ?',
            ['foo'],
            $query
        );
    }

    public function testAlias(): void
    {
        $query = $this->grammar->alias(new Sql('c1 + ?', [1]), 'a1');
        $this->assertQueryIs(
            'c1 + ? AS a1',
            [1],
            $query
        );
    }

    public function testIdentifier(): void
    {
        $this->assertEquals('`foo`', $this->grammar->identifier('foo'));
        $this->assertEquals('```foo```', $this->grammar->identifier('`foo`'));
    }

    /**
     * @dataProvider providerCompileSelect
     */
    public function testCompileSelect(string $prefix, array $select, array $from, array $join, ?Sql $where, array $groupBy, ?Sql $having, array $window, array $orderBy, ?int $limit, ?int $offset, string $suffix, array $union, string $expectedSql, array $expectedBindings): void
    {
        $query = $this->grammar->selectStatement($prefix, $select, $from, $join, $where, $groupBy, $having, $window, $orderBy, $limit, $offset, $suffix, $union);
        $this->assertEquals($expectedSql, $query->getSql());
        $this->assertEquals($expectedBindings, $query->getBindings());
    }

    public static function providerCompileSelect(): array
    {
        return [
            [
                'SELECT',
                [new Sql('c1'), new Sql('c2')],
                [new Sql('t1'), new Sql('t2')],
                [new Sql('JOIN t3 ON t1.c1 = t3.c1'), new Sql('JOIN t4 ON t1.c1 = t4.c1')],
                new Sql('c1 = ? AND c2 = ?', ['foo', 'bar']),
                [new Sql('c3'), new Sql('c4')],
                new Sql('c5 = ?', ['baz']),
                [new Sql('w AS (PARTITION BY c1)', [])],
                [new Sql('c6'), new Sql('c7')],
                100,
                200,
                'FOR UPDATE',
                [new Sql('UNION ALL (SELECT c1, c2 FROM t5 WHERE c1 = ?)', ['qux'])],
                'SELECT c1, c2 FROM t1, t2 JOIN t3 ON t1.c1 = t3.c1 JOIN t4 ON t1.c1 = t4.c1 WHERE c1 = ? AND c2 = ? GROUP BY c3, c4 HAVING c5 = ? WINDOW w AS (PARTITION BY c1) ORDER BY c6, c7 LIMIT ? OFFSET ? FOR UPDATE UNION ALL (SELECT c1, c2 FROM t5 WHERE c1 = ?)',
                ['foo', 'bar', 'baz', 100, 200, 'qux'],
            ],
            [
                'SELECT',
                [],
                [new Sql('t1')],
                [],
                new Sql('c1 = ?', ['foo']),
                [],
                null,
                [],
                [],
                null,
                null,
                '',
                [],
                'SELECT * FROM t1 WHERE c1 = ?',
                ['foo'],
            ],
            [
                'SELECT',
                [new Sql('1')],
                [],
                [],
                null,
                [],
                null,
                [],
                [],
                null,
                null,
                '',
                [],
                'SELECT 1',
                [],
            ],
        ];
    }

    /**
     * @dataProvider providerCompileInsert
     */
    public function testCompileInsert(string $prefix, string $table, array $columns, array $values, ?Sql $select, string $expectedSql, array $expectedBindings): void
    {
        $query = $this->grammar->insertStatement($prefix, $table, $columns, $values, $select);
        $this->assertEquals($expectedSql, $query->getSql());
        $this->assertEquals($expectedBindings, $query->getBindings());
    }

    public static function providerCompileInsert(): array
    {
        return [
            [
                'INSERT',
                't1',
                ['c1', 'c2', 'c3'],
                [[Sql::value('foo'), Sql::value('bar'), Sql::value('baz')]],
                null,
                'INSERT INTO t1 (c1, c2, c3) VALUES (?, ?, ?)',
                ['foo', 'bar', 'baz'],
            ],
            [
                'INSERT',
                't1',
                ['c1', 'c2', 'c3'],
                [],
                new Sql('SELECT c1, c2, c3 FROM t1 WHERE c1 = ?', ['foo']),
                'INSERT INTO t1 (c1, c2, c3) SELECT c1, c2, c3 FROM t1 WHERE c1 = ?',
                ['foo'],
            ],
        ];
    }

    /**
     * @dataProvider providerCompileUpdate
     */
    public function testCompileUpdate(string $prefix, string $table, array $update, ?Sql $where, string $expectedSql, array $expectedBindings): void
    {
        $query = $this->grammar->updateStatement($prefix, $table, $update, $where);
        $this->assertEquals($expectedSql, $query->getSql());
        $this->assertEquals($expectedBindings, $query->getBindings());
    }

    public static function providerCompileUpdate(): array
    {
        return [
            [
                'UPDATE',
                't1',
                ['t1.c1' => Sql::value('foo'), 't1.c2' => Sql::value('bar'), 't1.c3' => Sql::value('baz')],
                new Sql('t1.c1 = ?', [123]),
                'UPDATE t1 SET t1.c1 = ?, t1.c2 = ?, t1.c3 = ? WHERE t1.c1 = ?',
                ['foo', 'bar', 'baz', 123],
            ],
            [
                'UPDATE',
                't1',
                [],
                new Sql('t1.c1 = ?', [123]),
                'UPDATE t1 WHERE t1.c1 = ?',
                [123],
            ],
        ];
    }

    /**
     * @dataProvider providerCompileDelete
     */
    public function testCompileDelete(string $prefix, string $from, Sql $where = null, $expectedSql, array $expectedBindings): void
    {
        $query = $this->grammar->deleteStatement($prefix, $from, $where);
        $this->assertEquals($expectedSql, $query->getSql());
        $this->assertEquals($expectedBindings, $query->getBindings());
    }

    public static function providerCompileDelete(): array
    {
        return [
            [
                'DELETE',
                't1',
                new Sql('t1.c1 = ?', [123]),
                'DELETE FROM t1 WHERE t1.c1 = ?',
                [123],
            ],
        ];
    }
}
