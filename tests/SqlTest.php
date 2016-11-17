<?php

namespace Emonkak\Orm\Tests;

use Emonkak\Orm\Sql;

class SqlTest extends \PHPUnit_Framework_TestCase
{
    public function testToString()
    {
        $sql = 'SELECT * FROM t1 WHERE (((((c1 IN (?, ?, ?)) AND (c2 = ?)) AND (c3 IS NOT ?)) AND (c4 = ?)) AND (c5 = ?)) ORDER BY c1';
        $bindings = [1, 2, 3, "'foo'", null, true, false];
        $expected = "SELECT * FROM t1 WHERE (((((c1 IN (1, 2, 3)) AND (c2 = '\\'foo\\'')) AND (c3 IS NOT NULL)) AND (c4 = 1)) AND (c5 = 0)) ORDER BY c1";
        $this->assertSame($expected, (string) new Sql($sql, $bindings));

        $sql = 'SELECT * FROM t1 WHERE (c1 = ?)';
        $bindings = [hex2bin('ff')];
        $expected = "SELECT * FROM t1 WHERE (c1 = x'ff')";
        $this->assertSame($expected, (string) new Sql($sql, $bindings));
    }

    public function testValues()
    {
        $query = Sql::values([1, 2, 3]);
        $this->assertSame('(?, ?, ?)', $query->getSql());
        $this->assertSame([1, 2, 3], $query->getBindings());
    }

    public function testAppend()
    {
        $query = (new Sql('SELECT'))
            ->append('*')
            ->append('FROM')
            ->append('t1')
            ->append('WHERE')
            ->append('c1 = ?', [123]);
        $this->assertSame('SELECT * FROM t1 WHERE c1 = ?', $query->getSql());
        $this->assertSame([123], $query->getBindings());
    }

    public function testAppendSql()
    {
        $query = (new Sql('SELECT'))
            ->append('*')
            ->append('FROM')
            ->append('t1')
            ->append('WHERE')
            ->appendSql(new Sql('c1 IN (?, ?, ?)', [1, 2, 3]));
        $this->assertSame('SELECT * FROM t1 WHERE c1 IN (?, ?, ?)', $query->getSql());
        $this->assertSame([1, 2, 3], $query->getBindings());
    }

    public function testAppendBuilder()
    {
        $query = (new Sql('SELECT'))
            ->append('*')
            ->append('FROM')
            ->append('t1')
            ->append('WHERE c1 IN')
            ->appendBuilder(new Sql('SELECT c1 FROM t2 WHERE c1 = ?', [123]));
        $this->assertSame('SELECT * FROM t1 WHERE c1 IN (SELECT c1 FROM t2 WHERE c1 = ?)', $query->getSql());
        $this->assertSame([123], $query->getBindings());
    }

    public function testPrepend()
    {
        $query = (new Sql('SELECT'))
            ->append('*')
            ->append('FROM')
            ->append('t1')
            ->append('WHERE')
            ->append('c1 = ?', [123])
            ->prepend('EXPLAIN');
        $this->assertSame('EXPLAIN SELECT * FROM t1 WHERE c1 = ?', $query->getSql());
        $this->assertSame([123], $query->getBindings());
    }

    public function testPrependSql()
    {
        $query = (new Sql('UNION ALL'))
            ->appendSql(new Sql('SELECT * FROM t1 WHERE c1 = ?', [123]))
            ->prependSql(new Sql('SELECT * FROM t2 WHERE c1 = ?', [456]));
        $this->assertSame('SELECT * FROM t2 WHERE c1 = ? UNION ALL SELECT * FROM t1 WHERE c1 = ?', $query->getSql());
        $this->assertSame([456, 123], $query->getBindings());
    }

    public function testPrependBuilder()
    {
        $query = (new SQL('UNION ALL'))
            ->appendBuilder(new Sql('SELECT * FROM t1 WHERE c1 = ?', [123]))
            ->prependBuilder(new Sql('SELECT * FROM t2 WHERE c1 = ?', [456]));
        $this->assertSame('(SELECT * FROM t2 WHERE c1 = ?) UNION ALL (SELECT * FROM t1 WHERE c1 = ?)', $query->getSql());
        $this->assertSame([456, 123], $query->getBindings());
    }

    public function testEnclosed()
    {
        $query = (new Sql('SELECT * FROM t1 WHERE c1 = ?', [123]))
            ->enclosed()
            ->prepend('SELCET * FROM');
        $this->assertSame('SELCET * FROM (SELECT * FROM t1 WHERE c1 = ?)', $query->getSql());
        $this->assertSame([123], $query->getBindings());
    }
}
