<?php

namespace Emonkak\Orm\Tests;

use Emonkak\Orm\Sql;

/**
 * @covers Emonkak\Orm\Sql
 */
class SqlTest extends \PHPUnit_Framework_TestCase
{
    public function testFormat()
    {
        $query = Sql::format('SELECT * FROM t1 WHERE c1 = %s AND c2 IN %s', Sql::value(123), Sql::values(['foo', 'bar', 'baz']));
        $this->assertEquals('SELECT * FROM t1 WHERE c1 = ? AND c2 IN (?, ?, ?)', $query->getSql());
        $this->assertEquals([123, 'foo', 'bar', 'baz'], $query->getBindings());
    }

    public function testLiteral()
    {
        $query = Sql::literal('c1 = c2');
        $this->assertEquals('c1 = c2', $query->getSql());
        $this->assertEquals([], $query->getBindings());
    }

    public function testValue()
    {
        $query = Sql::value(123);
        $this->assertEquals('?', $query->getSql());
        $this->assertEquals([123], $query->getBindings());
    }

    public function testValues()
    {
        $query = Sql::values([1, 2, 3]);
        $this->assertEquals('(?, ?, ?)', $query->getSql());
        $this->assertEquals([1, 2, 3], $query->getBindings());
    }

    public function testToString()
    {
        $sql = 'SELECT * FROM t1 WHERE (((((c1 IN (?, ?, ?)) AND (c2 = ?)) AND (c3 IS NOT ?)) AND (c4 = ?)) AND (c5 = ?)) ORDER BY c1';
        $bindings = [1, 2, 3, "'foo'", null, true, false];
        $expected = "SELECT * FROM t1 WHERE (((((c1 IN (1, 2, 3)) AND (c2 = '\\'foo\\'')) AND (c3 IS NOT NULL)) AND (c4 = 1)) AND (c5 = 0)) ORDER BY c1";
        $this->assertEquals($expected, (string) new Sql($sql, $bindings));

        $sql = 'SELECT * FROM t1 WHERE (c1 = ?)';
        $bindings = [hex2bin('ff')];
        $expected = "SELECT * FROM t1 WHERE (c1 = x'ff')";
        $this->assertEquals($expected, (string) new Sql($sql, $bindings));
    }

    public function testAppend()
    {
        $query = Sql::literal('SELECT')
            ->append('*')
            ->append('FROM')
            ->append('t1')
            ->append('WHERE')
            ->append('c1 = ?', [123]);
        $this->assertEquals('SELECT * FROM t1 WHERE c1 = ?', $query->getSql());
        $this->assertEquals([123], $query->getBindings());
    }

    public function testAppendSql()
    {
        $query = Sql::literal('SELECT')
            ->append('*')
            ->append('FROM')
            ->append('t1')
            ->append('WHERE')
            ->appendSql(new Sql('c1 IN (?, ?, ?)', [1, 2, 3]));
        $this->assertEquals('SELECT * FROM t1 WHERE c1 IN (?, ?, ?)', $query->getSql());
        $this->assertEquals([1, 2, 3], $query->getBindings());

        $query = Sql::literal('INSERT INTO t1 VALUES')
            ->appendSql(Sql::values([1, 2, 3]))
            ->appendSql(Sql::values([4, 5, 6]), ', ')
            ->appendSql(Sql::values([7, 8, 9]), ', ');
        $this->assertEquals('INSERT INTO t1 VALUES (?, ?, ?), (?, ?, ?), (?, ?, ?)', $query->getSql());
        $this->assertEquals([1, 2, 3, 4, 5, 6, 7, 8, 9], $query->getBindings());
    }

    public function testAppendBuilder()
    {
        $query = Sql::literal('SELECT')
            ->append('*')
            ->append('FROM')
            ->append('t1')
            ->append('WHERE c1 IN')
            ->appendBuilder(new Sql('SELECT c1 FROM t2 WHERE c1 = ?', [123]));
        $this->assertEquals('SELECT * FROM t1 WHERE c1 IN (SELECT c1 FROM t2 WHERE c1 = ?)', $query->getSql());
        $this->assertEquals([123], $query->getBindings());
    }

    public function testPrepend()
    {
        $query = Sql::literal('SELECT')
            ->append('*')
            ->append('FROM')
            ->append('t1')
            ->append('WHERE')
            ->append('c1 = ?', [123])
            ->prepend('EXPLAIN');
        $this->assertEquals('EXPLAIN SELECT * FROM t1 WHERE c1 = ?', $query->getSql());
        $this->assertEquals([123], $query->getBindings());
    }

    public function testPrependSql()
    {
        $query = Sql::literal('UNION ALL')
            ->appendSql(new Sql('SELECT * FROM t1 WHERE c1 = ?', [123]))
            ->prependSql(new Sql('SELECT * FROM t2 WHERE c1 = ?', [456]));
        $this->assertEquals('SELECT * FROM t2 WHERE c1 = ? UNION ALL SELECT * FROM t1 WHERE c1 = ?', $query->getSql());
        $this->assertEquals([456, 123], $query->getBindings());

        $query = Sql::literal('INSERT INTO t1 VALUES')
            ->appendSql(
                Sql::values([1, 2, 3])
                    ->prependSql(Sql::values([4, 5, 6]), ', ')
                    ->prependSql(Sql::values([7, 8, 9]), ', ')
            );
        $this->assertEquals('INSERT INTO t1 VALUES (?, ?, ?), (?, ?, ?), (?, ?, ?)', $query->getSql());
        $this->assertEquals([7, 8, 9, 4, 5, 6, 1, 2, 3], $query->getBindings());
    }

    public function testPrependBuilder()
    {
        $query = Sql::literal('UNION ALL')
            ->appendBuilder(new Sql('SELECT * FROM t1 WHERE c1 = ?', [123]))
            ->prependBuilder(new Sql('SELECT * FROM t2 WHERE c1 = ?', [456]));
        $this->assertEquals('(SELECT * FROM t2 WHERE c1 = ?) UNION ALL (SELECT * FROM t1 WHERE c1 = ?)', $query->getSql());
        $this->assertEquals([456, 123], $query->getBindings());
    }

    public function testEnclosed()
    {
        $query = (new Sql('SELECT * FROM t1 WHERE c1 = ?', [123]))
            ->enclosed()
            ->prepend('SELCET * FROM');
        $this->assertEquals('SELCET * FROM (SELECT * FROM t1 WHERE c1 = ?)', $query->getSql());
        $this->assertEquals([123], $query->getBindings());
    }
}
