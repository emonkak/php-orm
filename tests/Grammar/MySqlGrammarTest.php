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

    /**
     * @var MySqlGrammar
     */
    private $grammar;

    public function setUp()
    {
        $this->grammar = new MySqlGrammar();
    }


    /**
     * @dataProvider providerLift
     */
    public function testLift($value, $expectedSql, array $expectedBindings)
    {
        $query = $this->grammar->lift($value);
        $this->assertQueryIs($expectedSql, $expectedBindings, $query);
    }

    public function providerLift()
    {
        return [
            [new Sql('?', ['foo']), '?', ['foo']],
            [$this->createSelectBuilder()->from('t1')->where('c1', '=', 'foo'), '(SELECT * FROM t1 WHERE (c1 = ?))', ['foo']],
            ['foo', 'foo', []],
        ];
    }

    /**
     * @dataProvider providerLiftThrowsUnexpectedValueException
     *
     * @expectedException UnexpectedValueException
     */
    public function testLiftThrowsUnexpectedValueException($value)
    {
        $this->grammar->lift($value);
    }

    public function providerLiftThrowsUnexpectedValueException()
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
     * @dataProvider providerLiftValue
     */
    public function testLiftValue($value, $expectedSql, array $expectedBindings)
    {
        $query = $this->grammar->liftValue($value);
        $this->assertQueryIs($expectedSql, $expectedBindings, $query);
    }

    public function providerLiftValue()
    {
        return [
            [new Sql('?', ['foo']), '?', ['foo']],
            [$this->createSelectBuilder()->from('t1')->where('c1', '=', 'foo'), '(SELECT * FROM t1 WHERE (c1 = ?))', ['foo']],
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
     * @dataProvider providerLiftValueThrowsUnexpectedValueException
     *
     * @expectedException UnexpectedValueException
     */
    public function testLiftValueThrowsUnexpectedValueException($value)
    {
        $this->grammar->liftValue($value);
    }

    public function providerLiftValueThrowsUnexpectedValueException()
    {
        return [
            [new \stdClass()],
        ];
    }

    /**
     * @dataProvider providerOperator
     */
    public function testOperator($operator, $lhsSql, array $lhsBindings, $rhsSql, array $rhsBindings, $expectedSql, array $expectedBindings)
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

    public function providerOperator()
    {
        return [
            ['=', 'c1', [],'?', ['foo'], '(c1 = ?)', ['foo']],
            ['!=','c1', [], '?', ['foo'], '(c1 != ?)', ['foo']],
            ['<>','c1', [], '?', ['foo'], '(c1 <> ?)', ['foo']],
            ['<', 'c1', [],'?', ['foo'], '(c1 < ?)', ['foo']],
            ['<=','c1', [], '?', ['foo'], '(c1 <= ?)', ['foo']],
            ['>', 'c1', [],'?', ['foo'], '(c1 > ?)', ['foo']],
            ['>=','c1', [], '?', ['foo'], '(c1 >= ?)', ['foo']],
            ['IN','c1', [], '(?, ?, ?)', ['foo', 'bar', 'baz'], '(c1 IN (?, ?, ?))', ['foo', 'bar', 'baz']],
            ['NOT IN', 'c1', [], '(?, ?, ?)', ['foo', 'bar', 'baz'], '(c1 NOT IN (?, ?, ?))', ['foo', 'bar', 'baz']],
            ['LIKE', 'c1', [], '?', ['foo'], '(c1 LIKE ?)', ['foo']],
            ['NOT LIKE', 'c1', [], '?', ['foo'], '(c1 NOT LIKE ?)', ['foo']],
            ['AND', '(c1 = ?)', ['foo'], '(c2 = ?)', ['bar'], '((c1 = ?) AND (c2 = ?))', ['foo', 'bar']],
            ['OR', '(c1 = ?)', ['foo'], '(c2 = ?)', ['bar'], '((c1 = ?) OR (c2 = ?))', ['foo', 'bar']],
        ];
    }

    /**
     * @dataProvider providerBetweenOperator
     */
    public function testBetweenOperator($operator, $lhsSql, array $lhsBindings, $startSql, array $startBindings, $endSql, array $endBindings, $expectedSql, array $expectedBindings)
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

    public function providerBetweenOperator()
    {
        return [
            ['BETWEEN', 'c1', [], '?', [123], '?', [456], '(c1 BETWEEN ? AND ?)', [123, 456]],
            ['NOT BETWEEN', 'c1', [], '?', [123], '?', [456], '(c1 NOT BETWEEN ? AND ?)', [123, 456]],
        ];
    }

    /**
     * @dataProvider providerUnaryOperator
     */
    public function testUnaryOperator($operator, $lhsSql, array $lhsBindings, $expectedSql, array $expectedBindings)
    {
        $lhs = new Sql($lhsSql, $lhsBindings);

        $query = $this->grammar->unaryOperator($operator, $lhs);

        $this->assertQueryIs(
            $expectedSql,
            $expectedBindings,
            $query
        );
    }

    public function providerUnaryOperator()
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

    public function testJoin()
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

    public function testOrdering()
    {
        $query = $this->grammar->ordering(new Sql('c1 + ?', [1]), 'DESC');
        $this->assertQueryIs(
            'c1 + ? DESC',
            [1],
            $query
        );
    }

    public function testUnion()
    {
        $query = $this->grammar->union(new Sql('SELECT * FROM t1 WHERE c1 = ?', ['foo']), 'UNION ALL');
        $this->assertQueryIs(
            'UNION ALL SELECT * FROM t1 WHERE c1 = ?',
            ['foo'],
            $query
        );
    }

    public function testAlias()
    {
        $query = $this->grammar->alias(new Sql('c1 + ?', [1]), 'a1');
        $this->assertQueryIs(
            'c1 + ? AS a1',
            [1],
            $query
        );
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
        $query = $this->grammar->selectStatement($prefix, $select, $from, $join, $where, $groupBy, $having, $orderBy, $limit, $offset, $suffix, $union);
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
        $query = $this->grammar->insertStatement($prefix, $table, $columns, $values, $select);
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
        $query = $this->grammar->updateStatement($prefix, $table, $update, $where);
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
        $query = $this->grammar->deleteStatement($prefix, $from, $where);
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
