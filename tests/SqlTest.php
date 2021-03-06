<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests;

use Emonkak\Orm\Sql;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Emonkak\Orm\Sql
 */
class SqlTest extends TestCase
{
    use QueryBuilderTestTrait;

    public function testFormat(): void
    {
        $query = Sql::format('SELECT * FROM t1 WHERE c1 = %s AND c2 IN %s', Sql::value(123), Sql::values(['foo', 'bar', 'baz']));
        $this->assertQueryIs(
            'SELECT * FROM t1 WHERE c1 = ? AND c2 IN (?, ?, ?)',
            [123, 'foo', 'bar', 'baz'],
            $query
        );
    }

    public function testJoin(): void
    {
        $query = Sql::join(
            ' AND ',
            [
                new Sql('c1 = ?', [123]),
                new Sql('c2 = ?', [456]),
                new Sql('c3 = ?', [789]),
            ]
        );

        $this->assertQueryIs(
            'c1 = ? AND c2 = ? AND c3 = ?',
            [123, 456, 789],
            $query
        );
    }

    public function testValue(): void
    {
        $query = Sql::value(123);
        $this->assertQueryIs(
            '?',
            [123],
            $query
        );
    }

    public function testValues(): void
    {
        $query = Sql::values([1, 2, 3]);
        $this->assertQueryIs(
            '(?, ?, ?)',
            [1, 2, 3],
            $query
        );
    }

    public function testAnd(): void
    {
        $query = Sql::and(
            Sql::or(
                new Sql('foo = ?', [1]),
                new Sql('bar = ?', [2])
            ),
            new Sql('qux = ?', [3]),
            new Sql('baz = ?', [4])
        );

        $this->assertQueryIs(
            '(((foo = ? OR bar = ?) AND qux = ?) AND baz = ?)',
            [1, 2, 3, 4],
            $query
        );
    }

    public function testToString(): void
    {
        $sql = 'SELECT * FROM t1 WHERE (((((c1 IN (?, ?, ?)) AND (c2 = ?)) AND (c3 IS NOT ?)) AND (c4 = ?)) AND (c5 = ?)) ORDER BY c1';
        $bindings = [1, 2, 3, "'foo'", null, true, false];
        $expected = "SELECT * FROM t1 WHERE (((((c1 IN (1, 2, 3)) AND (c2 = '\\'foo\\'')) AND (c3 IS NOT NULL)) AND (c4 = 1)) AND (c5 = 0)) ORDER BY c1";
        $this->assertEquals($expected, (string) new Sql($sql, $bindings));

        $sql = 'SELECT * FROM t1 WHERE (c1 = ?)';
        $bindings = [hex2bin('ff')];
        $expected = "SELECT * FROM t1 WHERE (c1 = x'ff')";
        $this->assertEquals($expected, (string) new Sql($sql, $bindings));

        $sql = 'SELECT * FROM t1 WHERE (c1 = ?)';
        $bindings = [new \stdClass()];
        $expected = "SELECT * FROM t1 WHERE (c1 = '<stdClass>')";
        $this->assertEquals($expected, (string) new Sql($sql, $bindings));
    }

    public function testAppend(): void
    {
        $query = (new Sql('SELECT'))
            ->append('*')
            ->append('FROM')
            ->append('t1')
            ->append('WHERE')
            ->append('c1 = ?', [123]);
        $this->assertQueryIs(
            'SELECT * FROM t1 WHERE c1 = ?',
            [123],
            $query
        );
    }

    public function testAppendQuery(): void
    {
        $query = (new Sql('SELECT'))
            ->append('*')
            ->append('FROM')
            ->append('t1')
            ->append('WHERE')
            ->appendQuery(new Sql('c1 IN (?, ?, ?)', [1, 2, 3]));
        $this->assertQueryIs(
            'SELECT * FROM t1 WHERE c1 IN (?, ?, ?)',
            [1, 2, 3],
            $query
        );

        $query = (new Sql('INSERT INTO t1 VALUES'))
            ->appendQuery(Sql::values([1, 2, 3]))
            ->appendQuery(Sql::values([4, 5, 6]), ', ')
            ->appendQuery(Sql::values([7, 8, 9]), ', ');
        $this->assertQueryIs(
            'INSERT INTO t1 VALUES (?, ?, ?), (?, ?, ?), (?, ?, ?)',
            [1, 2, 3, 4, 5, 6, 7, 8, 9],
            $query
        );
    }

    public function testAppendQueryBuilder(): void
    {
        $query = (new Sql('SELECT'))
            ->append('*')
            ->append('FROM')
            ->append('t1')
            ->append('WHERE c1 IN')
            ->appendQueryBuilder(new Sql('SELECT c1 FROM t2 WHERE c1 = ?', [123]));
        $this->assertQueryIs(
            'SELECT * FROM t1 WHERE c1 IN (SELECT c1 FROM t2 WHERE c1 = ?)',
            [123],
            $query
        );
    }

    public function testPrepend(): void
    {
        $query = (new Sql('SELECT'))
            ->append('*')
            ->append('FROM')
            ->append('t1')
            ->append('WHERE')
            ->append('c1 = ?', [123])
            ->prepend('EXPLAIN');
        $this->assertQueryIs(
            'EXPLAIN SELECT * FROM t1 WHERE c1 = ?',
            [123],
            $query
        );
    }

    public function testPrependQuery(): void
    {
        $query = (new Sql('UNION ALL'))
            ->appendQuery(new Sql('SELECT * FROM t1 WHERE c1 = ?', [123]))
            ->prependQuery(new Sql('SELECT * FROM t2 WHERE c1 = ?', [456]));
        $this->assertQueryIs(
            'SELECT * FROM t2 WHERE c1 = ? UNION ALL SELECT * FROM t1 WHERE c1 = ?',
            [456, 123],
            $query
        );

        $query = (new Sql('INSERT INTO t1 VALUES'))
            ->appendQuery(
                Sql::values([1, 2, 3])
                    ->prependQuery(Sql::values([4, 5, 6]), ', ')
                    ->prependQuery(Sql::values([7, 8, 9]), ', ')
            );
        $this->assertQueryIs(
            'INSERT INTO t1 VALUES (?, ?, ?), (?, ?, ?), (?, ?, ?)',
            [7, 8, 9, 4, 5, 6, 1, 2, 3],
            $query
        );
    }

    public function testPrependQueryBuilder(): void
    {
        $query = (new Sql('UNION ALL'))
            ->appendQueryBuilder(new Sql('SELECT * FROM t1 WHERE c1 = ?', [123]))
            ->prependQueryBuilder(new Sql('SELECT * FROM t2 WHERE c1 = ?', [456]));
        $this->assertQueryIs(
            '(SELECT * FROM t2 WHERE c1 = ?) UNION ALL (SELECT * FROM t1 WHERE c1 = ?)',
            [456, 123],
            $query
        );
    }

    public function testEnclosed(): void
    {
        $query = (new Sql('SELECT * FROM t1 WHERE c1 = ?', [123]))
            ->enclosed()
            ->prepend('SELCET * FROM');
        $this->assertQueryIs(
            'SELCET * FROM (SELECT * FROM t1 WHERE c1 = ?)',
            [123],
            $query
        );
    }
}
