<?php

namespace Emonkak\Orm\Tests\Grammar;

use Emonkak\Orm\Grammar\DefaultGrammar;
use Emonkak\Orm\SelectBuilder;
use Emonkak\Orm\Sql;

/**
 * @covers Emonkak\Orm\Grammar\DefaultGrammar
 */
class DefaultGrammarTest extends \PHPUnit_Framework_TestCase
{
    private $grammar;

    public function setUp()
    {
        $this->grammar = DefaultGrammar::getInstance();
    }

    public function testAlias()
    {
        $query = $this->grammar->alias(new Sql('c1 + ?', [1]), 'a1');
        $this->assertEquals('c1 + ? AS a1', $query->getSql());
        $this->assertEquals([1], $query->getBindings());
    }

    public function testOrder()
    {
        $query = $this->grammar->order(new Sql('c1 + ?', [1]), 'DESC');
        $this->assertEquals('c1 + ? DESC', $query->getSql());
        $this->assertEquals([1], $query->getBindings());
    }

    public function testJoin()
    {
        $query = $this->grammar->join(Sql::literal('t2'), new Sql('t1.c1 = t2.c1 AND t2.c2 = ?', ['foo']), 'LEFT INNER JOIN');
        $this->assertEquals('LEFT INNER JOIN t2 ON t1.c1 = t2.c1 AND t2.c2 = ?', $query->getSql());
        $this->assertEquals(['foo'], $query->getBindings());

        $query = $this->grammar->join(Sql::literal('t2'), null, 'CROSS JOIN');
        $this->assertEquals('CROSS JOIN t2', $query->getSql());
        $this->assertEquals([], $query->getBindings());
    }

    public function testUnion()
    {
        $query = $this->grammar->union(new Sql('SELECT * FROM t1 WHERE c1 = ?', ['foo']), 'UNION ALL');
        $this->assertEquals('UNION ALL SELECT * FROM t1 WHERE c1 = ?', $query->getSql());
        $this->assertEquals(['foo'], $query->getBindings());
    }

    /**
     * @dataProvider providerOperator
     */
    public function testOperator($operator, Sql $lhs, Sql $rhs, $expectedSql, $expectedBindings)
    {
        $query = $this->grammar->operator($operator, $lhs, $rhs);
        $this->assertEquals($expectedSql, $query->getSql());
        $this->assertEquals($expectedBindings, $query->getBindings());
    }

    public function providerOperator()
    {
        return [
            ['=', Sql::literal('c1'), Sql::value('foo'), '(c1 = ?)', ['foo']],
            ['!=', Sql::literal('c1'), Sql::value('foo'), '(c1 != ?)', ['foo']],
            ['<>', Sql::literal('c1'), Sql::value('foo'), '(c1 <> ?)', ['foo']],
            ['<', Sql::literal('c1'), Sql::value('foo'), '(c1 < ?)', ['foo']],
            ['<=', Sql::literal('c1'), Sql::value('foo'), '(c1 <= ?)', ['foo']],
            ['>', Sql::literal('c1'), Sql::value('foo'), '(c1 > ?)', ['foo']],
            ['>=', Sql::literal('c1'), Sql::value('foo'), '(c1 >= ?)', ['foo']],
            ['IN', Sql::literal('c1'), Sql::values(['foo', 'bar', 'baz']), '(c1 IN (?, ?, ?))', ['foo', 'bar', 'baz']],
            ['NOT IN', Sql::literal('c1'), Sql::values(['foo', 'bar', 'baz']), '(c1 NOT IN (?, ?, ?))', ['foo', 'bar', 'baz']],
            ['LIKE', Sql::literal('c1'), Sql::value('foo'), '(c1 LIKE ?)', ['foo']],
            ['NOT LIKE', Sql::literal('c1'), Sql::value('foo'), '(c1 NOT LIKE ?)', ['foo']],
            ['AND', new Sql('(c1 = ?)', ['foo']), new Sql('(c2 = ?)', ['bar']), '((c1 = ?) AND (c2 = ?))', ['foo', 'bar']],
            ['OR', new Sql('(c1 = ?)', ['foo']), new Sql('(c2 = ?)', ['bar']), '((c1 = ?) OR (c2 = ?))', ['foo', 'bar']],
        ];
    }

    /**
     * @expectedException UnexpectedValueException
     */
    public function testOperatorThrowsUnexpectedValueException()
    {
        $this->grammar->operator('unknown', Sql::literal('c1'), Sql::value('foo'));
    }

    /**
     * @dataProvider providerBetweenOperator
     */
    public function testBetweenOperator($operator, Sql $lhs, Sql $rhs1, Sql $rhs2, $expectedSql, $expectedBindings)
    {
        $query = $this->grammar->betweenOperator($operator, $lhs, $rhs1, $rhs2);
        $this->assertEquals($expectedSql, $query->getSql());
        $this->assertEquals($expectedBindings, $query->getBindings());
    }

    public function providerBetweenOperator()
    {
        return [
            ['BETWEEN', Sql::literal('c1'), Sql::value('foo'), Sql::value('bar'), '(c1 BETWEEN ? AND ?)', ['foo', 'bar']],
            ['NOT BETWEEN', Sql::literal('c1'), Sql::value('foo'), Sql::value('bar'), '(c1 NOT BETWEEN ? AND ?)', ['foo', 'bar']],
        ];
    }

    /**
     * @expectedException UnexpectedValueException
     */
    public function testBetweenOperatorThrowsUnexpectedValueException()
    {
        $this->grammar->betweenOperator('unknown', Sql::literal('c1'), Sql::value('foo'), Sql::value('bar'));
    }

    /**
     * @dataProvider providerUnaryOperator
     */
    public function testUnaryOperator($operator, Sql $lhs, $expectedSql, $expectedBindings)
    {
        $query = $this->grammar->unaryOperator($operator, $lhs);
        $this->assertEquals($expectedSql, $query->getSql());
        $this->assertEquals($expectedBindings, $query->getBindings());
    }

    public function providerUnaryOperator()
    {
        return [
            ['NOT', Sql::literal('c1'), '(NOT c1)', []],
            ['IS NULL', Sql::literal('c1'), '(c1 IS NULL)', []],
            ['IS NOT NULL', Sql::literal('c1'), '(c1 IS NOT NULL)', []],
            ['EXISTS', new Sql('(SELECT * FROM t1 WHERE c1 = ?)', ['foo']), '(EXISTS (SELECT * FROM t1 WHERE c1 = ?))', ['foo']],
            ['NOT EXISTS', new Sql('(SELECT * FROM t1 WHERE c1 = ?)', ['foo']), '(NOT EXISTS (SELECT * FROM t1 WHERE c1 = ?))', ['foo']],
            ['ALL', new Sql('(SELECT * FROM t1 WHERE c1 = ?)', ['foo']), '(ALL (SELECT * FROM t1 WHERE c1 = ?))', ['foo']],
            ['NOT ALL', new Sql('(SELECT * FROM t1 WHERE c1 = ?)', ['foo']), '(NOT ALL (SELECT * FROM t1 WHERE c1 = ?))', ['foo']],
            ['ANY', new Sql('(SELECT * FROM t1 WHERE c1 = ?)', ['foo']), '(ANY (SELECT * FROM t1 WHERE c1 = ?))', ['foo']],
            ['NOT ANY', new Sql('(SELECT * FROM t1 WHERE c1 = ?)', ['foo']), '(NOT ANY (SELECT * FROM t1 WHERE c1 = ?))', ['foo']],
            ['SOME', new Sql('(SELECT * FROM t1 WHERE c1 = ?)', ['foo']), '(SOME (SELECT * FROM t1 WHERE c1 = ?))', ['foo']],
            ['NOT SOME', new Sql('(SELECT * FROM t1 WHERE c1 = ?)', ['foo']), '(NOT SOME (SELECT * FROM t1 WHERE c1 = ?))', ['foo']],
        ];
    }

    /**
     * @expectedException UnexpectedValueException
     */
    public function testUnaryOperatorThrowsUnexpectedValueException()
    {
        $this->grammar->unaryOperator('unknown', Sql::literal('c1'));
    }

    public function testIdentifier()
    {
        $this->assertEquals('`foo`', $this->grammar->identifier('foo'));
        $this->assertEquals('```foo```', $this->grammar->identifier('`foo`'));
    }

    /**
     * @dataProvider providerCompileSelect
     */
    public function testCompileSelect($prefix, array $select, array $from, array $join, Sql $where = null, array $groupBy, Sql $having = null, array $orderBy, $limit, $offset, $suffix, array $union, $expectedSql, array $expectedBindings)
    {
        $query = $this->grammar->compileSelect($prefix, $select, $from, $join, $where, $groupBy, $having, $orderBy, $limit, $offset, $suffix, $union);
        $this->assertEquals($expectedSql, $query->getSql());
        $this->assertEquals($expectedBindings, $query->getBindings());
    }

    public function providerCompileSelect()
    {
        return [
            [
                'SELECT',
                [Sql::literal('c1'), Sql::literal('c2')],
                [Sql::literal('t1'), Sql::literal('t2')],
                [Sql::literal('JOIN t3 ON t1.c1 = t3.c1'), Sql::literal('JOIN t4 ON t1.c1 = t4.c1')],
                new Sql('c1 = ? AND c2 = ?', ['foo', 'bar']),
                [Sql::literal('c3'), Sql::literal('c4')],
                new Sql('c5 = ?', ['baz']),
                [Sql::literal('c6'), Sql::literal('c7')],
                100,
                200,
                'FOR UPDATE',
                [new Sql('UNION ALL (SELECT c1, c2 FROM t5 WHERE c1 = ?)', ['qux'])],
                '(SELECT c1, c2 FROM t1, t2 JOIN t3 ON t1.c1 = t3.c1 JOIN t4 ON t1.c1 = t4.c1 WHERE c1 = ? AND c2 = ? GROUP BY c3, c4 HAVING c5 = ? ORDER BY c6, c7 LIMIT ? OFFSET ? FOR UPDATE) UNION ALL (SELECT c1, c2 FROM t5 WHERE c1 = ?)',
                ['foo', 'bar', 'baz', 100, 200, 'qux'],
            ],
            [
                'SELECT',
                [],
                [Sql::literal('t1')],
                [],
                new Sql('c1 = ?', ['foo']),
                [],
                null,
                [],
                null,
                null,
                null,
                [],
                'SELECT * FROM t1 WHERE c1 = ?',
                ['foo'],
            ],
            [
                'SELECT',
                [Sql::literal('1')],
                [],
                [],
                null,
                [],
                null,
                [],
                null,
                null,
                null,
                [],
                'SELECT 1',
                [],
            ],
        ];
    }

    /**
     * @dataProvider providerCompileInsert
     */
    public function testCompileInsert($prefix, $table, array $columns, array $values, Sql $select = null, $expectedSql, array $expectedBindings)
    {
        $query = $this->grammar->compileInsert($prefix, $table, $columns, $values, $select);
        $this->assertEquals($expectedSql, $query->getSql());
        $this->assertEquals($expectedBindings, $query->getBindings());
    }

    public function providerCompileInsert()
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
    public function testCompileUpdate($prefix, $table, array $update, Sql $where = null, $expectedSql, array $expectedBindings)
    {
        $query = $this->grammar->compileUpdate($prefix, $table, $update, $where);
        $this->assertEquals($expectedSql, $query->getSql());
        $this->assertEquals($expectedBindings, $query->getBindings());
    }

    public function providerCompileUpdate()
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
    public function testCompileDelete($prefix, $from, Sql $where = null, $expectedSql, array $expectedBindings)
    {
        $query = $this->grammar->compileDelete($prefix, $from, $where);
        $this->assertEquals($expectedSql, $query->getSql());
        $this->assertEquals($expectedBindings, $query->getBindings());
    }

    public function providerCompileDelete()
    {
        return [
            [
                'DELETE',
                't1',
                new Sql('t1.c1 = ?', [123]),
                'DELETE FROM t1 WHERE t1.c1 = ?',
                [123]
            ],
        ];
    }
}
