<?php

namespace Emonkak\Orm\QueryBuilder\Tests;

use Emonkak\Orm\QueryBuilder\Sql;

class SqlTest extends \PHPUnit_Framework_TestCase
{
    public function testToString()
    {
        $sql = "SELECT * FROM t1 WHERE (((((c1 IN (?, ?, ?)) AND (c2 = ?)) AND (c3 IS NOT ?)) AND (c4 = ?)) AND (c5 = ?)) ORDER BY c1";
        $bindings = [1, 2, 3, "'foo'", null, true, false];
        $expected = "SELECT * FROM t1 WHERE (((((c1 IN (1, 2, 3)) AND (c2 = '\\'foo\\'')) AND (c3 IS NOT NULL)) AND (c4 = 1)) AND (c5 = 0)) ORDER BY c1";
        $this->assertSame($expected, (string) new Sql($sql, $bindings));

        $sql = "SELECT * FROM t1 WHERE (c1 = ?)";
        $bindings = [hex2bin('ff')];
        $expected = "SELECT * FROM t1 WHERE (c1 = x'ff')";
        $this->assertSame($expected, (string) new Sql($sql, $bindings));
    }
}
