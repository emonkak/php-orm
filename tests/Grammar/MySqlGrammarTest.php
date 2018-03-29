<?php

namespace Emonkak\Orm\Tests\Grammar;

use Emonkak\Orm\Grammar\MySqlGrammar;
use Emonkak\Orm\SelectBuilder;
use Emonkak\Orm\Sql;
use Emonkak\Orm\Tests\QueryBuilderTestTrait;

/**
 * @covers Emonkak\Orm\Grammar\MySqlGrammar
 */
class MySqlGrammarTest extends \PHPUnit_Framework_TestCase
{
    use QueryBuilderTestTrait;

    public function testAlias()
    {
        $grammar = new MySqlGrammar();
        $query = $grammar->alias(new Sql('c1 + ?', [1]), 'a1');
        $this->assertQueryIs(
            'c1 + ? AS a1',
            [1],
            $query
        );
    }

    public function testOrder()
    {
        $grammar = new MySqlGrammar();
        $query = $grammar->order(new Sql('c1 + ?', [1]), 'DESC');
        $this->assertQueryIs(
            'c1 + ? DESC',
            [1],
            $query
        );
    }

    public function testJoin()
    {
        $grammar = new MySqlGrammar();
        $query = $grammar->join(new Sql('t2'), new Sql('t1.c1 = t2.c1 AND t2.c2 = ?', ['foo']), 'LEFT INNER JOIN');
        $this->assertQueryIs(
            'LEFT INNER JOIN t2 ON t1.c1 = t2.c1 AND t2.c2 = ?',
            ['foo'],
            $query
        );

        $query = $grammar->join(new Sql('t2'), null, 'CROSS JOIN');
        $this->assertQueryIs(
            'CROSS JOIN t2',
            [],
            $query
        );
    }

    public function testUnion()
    {
        $grammar = new MySqlGrammar();
        $query = $grammar->union(new Sql('SELECT * FROM t1 WHERE c1 = ?', ['foo']), 'UNION ALL');
        $this->assertQueryIs(
            'UNION ALL SELECT * FROM t1 WHERE c1 = ?',
            ['foo'],
            $query
        );
    }

    /**
     * @dataProvider providerOperator
     */
    public function testOperator($operator, Sql $lhs, Sql $rhs, $expectedSql, $expectedBindings)
    {
        $grammar = new MySqlGrammar();
        $query = $grammar->operator($operator, $lhs, $rhs);
        $this->assertEquals($expectedSql, $query->getSql());
        $this->assertEquals($expectedBindings, $query->getBindings());
    }

    public function providerOperator()
    {
        return [
            ['=', new Sql('c1'), Sql::value('foo'), '(c1 = ?)', ['foo']],
            ['!=', new Sql('c1'), Sql::value('foo'), '(c1 != ?)', ['foo']],
            ['<>', new Sql('c1'), Sql::value('foo'), '(c1 <> ?)', ['foo']],
            ['<', new Sql('c1'), Sql::value('foo'), '(c1 < ?)', ['foo']],
            ['<=', new Sql('c1'), Sql::value('foo'), '(c1 <= ?)', ['foo']],
            ['>', new Sql('c1'), Sql::value('foo'), '(c1 > ?)', ['foo']],
            ['>=', new Sql('c1'), Sql::value('foo'), '(c1 >= ?)', ['foo']],
            ['IN', new Sql('c1'), Sql::values(['foo', 'bar', 'baz']), '(c1 IN (?, ?, ?))', ['foo', 'bar', 'baz']],
            ['NOT IN', new Sql('c1'), Sql::values(['foo', 'bar', 'baz']), '(c1 NOT IN (?, ?, ?))', ['foo', 'bar', 'baz']],
            ['LIKE', new Sql('c1'), Sql::value('foo'), '(c1 LIKE ?)', ['foo']],
            ['NOT LIKE', new Sql('c1'), Sql::value('foo'), '(c1 NOT LIKE ?)', ['foo']],
            ['AND', new Sql('(c1 = ?)', ['foo']), new Sql('(c2 = ?)', ['bar']), '((c1 = ?) AND (c2 = ?))', ['foo', 'bar']],
            ['OR', new Sql('(c1 = ?)', ['foo']), new Sql('(c2 = ?)', ['bar']), '((c1 = ?) OR (c2 = ?))', ['foo', 'bar']],
        ];
    }

    /**
     * @expectedException UnexpectedValueException
     */
    public function testOperatorThrowsUnexpectedValueException()
    {
        $grammar = new MySqlGrammar();
        $grammar->operator('unknown', new Sql('c1'), Sql::value('foo'));
    }

    /**
     * @dataProvider providerBetweenOperator
     */
    public function testBetweenOperator($operator, Sql $lhs, Sql $rhs1, Sql $rhs2, $expectedSql, $expectedBindings)
    {
        $grammar = new MySqlGrammar();
        $query = $grammar->betweenOperator($operator, $lhs, $rhs1, $rhs2);
        $this->assertEquals($expectedSql, $query->getSql());
        $this->assertEquals($expectedBindings, $query->getBindings());
    }

    public function providerBetweenOperator()
    {
        return [
            ['BETWEEN', new Sql('c1'), Sql::value('foo'), Sql::value('bar'), '(c1 BETWEEN ? AND ?)', ['foo', 'bar']],
            ['NOT BETWEEN', new Sql('c1'), Sql::value('foo'), Sql::value('bar'), '(c1 NOT BETWEEN ? AND ?)', ['foo', 'bar']],
        ];
    }

    /**
     * @expectedException UnexpectedValueException
     */
    public function testBetweenOperatorThrowsUnexpectedValueException()
    {
        $grammar = new MySqlGrammar();
        $grammar->betweenOperator('unknown', new Sql('c1'), Sql::value('foo'), Sql::value('bar'));
    }

    /**
     * @dataProvider providerUnaryOperator
     */
    public function testUnaryOperator($operator, Sql $lhs, $expectedSql, $expectedBindings)
    {
        $grammar = new MySqlGrammar();
        $query = $grammar->unaryOperator($operator, $lhs);
        $this->assertEquals($expectedSql, $query->getSql());
        $this->assertEquals($expectedBindings, $query->getBindings());
    }

    public function providerUnaryOperator()
    {
        return [
            ['NOT', new Sql('c1'), '(NOT c1)', []],
            ['IS NULL', new Sql('c1'), '(c1 IS NULL)', []],
            ['IS NOT NULL', new Sql('c1'), '(c1 IS NOT NULL)', []],
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
        $grammar = new MySqlGrammar();
        $grammar->unaryOperator('unknown', new Sql('c1'));
    }

    public function testIdentifier()
    {
        $grammar = new MySqlGrammar();
        $this->assertEquals('`foo`', $grammar->identifier('foo'));
        $this->assertEquals('```foo```', $grammar->identifier('`foo`'));
    }

    /**
     * @dataProvider providerCompileSelect
     */
    public function testCompileSelect($prefix, array $select, array $from, array $join, Sql $where = null, array $groupBy, Sql $having = null, array $orderBy, $limit, $offset, $suffix, array $union, $expectedSql, array $expectedBindings)
    {
        $grammar = new MySqlGrammar();
        $query = $grammar->compileSelect($prefix, $select, $from, $join, $where, $groupBy, $having, $orderBy, $limit, $offset, $suffix, $union);
        $this->assertEquals($expectedSql, $query->getSql());
        $this->assertEquals($expectedBindings, $query->getBindings());
    }

    public function providerCompileSelect()
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
                [new Sql('c6'), new Sql('c7')],
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
                [new Sql('t1')],
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
                [new Sql('1')],
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
        $grammar = new MySqlGrammar();
        $query = $grammar->compileInsert($prefix, $table, $columns, $values, $select);
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
        $grammar = new MySqlGrammar();
        $query = $grammar->compileUpdate($prefix, $table, $update, $where);
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
        $grammar = new MySqlGrammar();
        $query = $grammar->compileDelete($prefix, $from, $where);
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
