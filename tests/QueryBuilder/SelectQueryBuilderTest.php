<?php

namespace Emonkak\Orm\QueryBuilder\Tests;

use Emonkak\Orm\QueryBuilder\Creteria;
use Emonkak\Orm\QueryBuilder\SelectQueryBuilder;

class SelectQueryBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testSelect()
    {
        list ($sql, $binds) = (new SelectQueryBuilder())
            ->select(1)
            ->build();
        $this->assertSame('SELECT ?', $sql);
        $this->assertSame([1], $binds);

        list ($sql, $binds) = (new SelectQueryBuilder())
            ->from('t1')
            ->build();
        $this->assertSame('SELECT * FROM t1', $sql);
        $this->assertSame([], $binds);

        list ($sql, $binds) = (new SelectQueryBuilder())
            ->select('c1')
            ->select('c2')
            ->from('t1')
            ->build();
        $this->assertSame('SELECT c1, c2 FROM t1', $sql);
        $this->assertSame([], $binds);

        list ($sql, $binds) = (new SelectQueryBuilder())
            ->select('c1', 'a1')
            ->select('c2', 'a2')
            ->from('t1')
            ->build();
        $this->assertSame('SELECT c1 AS a1, c2 AS a2 FROM t1', $sql);
        $this->assertSame([], $binds);

        list ($sql, $binds) = (new SelectQueryBuilder())
            ->select(Creteria::raw('? + 1', [100]), 'c1')
            ->from('t1')
            ->build();
        $this->assertSame('SELECT ? + 1 AS c1 FROM t1', $sql);
        $this->assertSame([100], $binds);

        $q = (new SelectQueryBuilder())->from('t2')->where('c1', '=', 'foo');
        list ($sql, $binds) = (new SelectQueryBuilder())
            ->select($q, 'c1')
            ->from('t1')
            ->build();
        $this->assertSame('SELECT (SELECT * FROM t2 WHERE (c1 = ?)) AS c1 FROM t1', $sql);
        $this->assertSame(['foo'], $binds);
    }

    public function testPrefix()
    {
        list ($sql, $binds) = (new SelectQueryBuilder())
            ->prefix('SELECT SQL_CALC_FOUND_ROWS')
            ->from('t1')
            ->build();
        $this->assertSame('SELECT SQL_CALC_FOUND_ROWS * FROM t1', $sql);
        $this->assertSame([], $binds);
    }

    public function testFrom()
    {
        list ($sql, $binds) = (new SelectQueryBuilder())
            ->select('c1')
            ->from('t1', 'a1')
            ->build();
        $this->assertSame('SELECT c1 FROM t1 AS a1', $sql);
        $this->assertSame([], $binds);

        list ($sql, $binds) = (new SelectQueryBuilder())
            ->from('t1')
            ->from('t2')
            ->build();
        $this->assertSame('SELECT * FROM t1, t2', $sql);
        $this->assertSame([], $binds);

        $q = (new SelectQueryBuilder())->from('t1');
        list ($sql, $binds) = (new SelectQueryBuilder())
            ->from($q, 'a1')
            ->build();
        $this->assertSame('SELECT * FROM (SELECT * FROM t1) AS a1', $sql);
        $this->assertSame([], $binds);
    }

    public function testJoin()
    {
        list ($sql, $binds) = (new SelectQueryBuilder())
            ->from('t1')
            ->join('t2', 't1.id = t2.id')
            ->join('t3', 't2.id = t3.id')
            ->build();
        $this->assertSame('SELECT * FROM t1 JOIN t2 ON t1.id = t2.id JOIN t3 ON t2.id = t3.id', $sql);
        $this->assertSame([], $binds);

        list ($sql, $binds) = (new SelectQueryBuilder())
            ->from('t1')
            ->leftJoin('t2', 't1.id = t2.id')
            ->join('t3', 't2.id = t3.id', null, 'INNER JOIN')
            ->build();
        $this->assertSame('SELECT * FROM t1 LEFT JOIN t2 ON t1.id = t2.id INNER JOIN t3 ON t2.id = t3.id', $sql, 'LEFT JOIN, INNER JOIN');
        $this->assertSame([], $binds);

        $q = (new SelectQueryBuilder())->from('t2');
        list ($sql, $binds) = (new SelectQueryBuilder())
            ->from('t1')
            ->join($q, 't1.id = t2.id', 't2')
            ->build();
        $this->assertSame('SELECT * FROM t1 JOIN (SELECT * FROM t2) AS t2 ON t1.id = t2.id', $sql);
        $this->assertSame([], $binds);


        list ($sql, $binds) = (new SelectQueryBuilder())
            ->from('t1')
            ->join(
                't2',
                Creteria::str('t1.id')->eq(Creteria::str('t2.id'))
                    ->_and(Creteria::str('t2.name')->in(['Yui Ogura', 'Kaori Ishihara']))
            )
            ->build();
        $this->assertSame('SELECT * FROM t1 JOIN t2 ON ((t1.id = t2.id) AND (t2.name IN (?, ?)))', $sql);
        $this->assertSame(['Yui Ogura', 'Kaori Ishihara'], $binds);
    }

    public function testWhereEqual()
    {
        list ($sql, $binds) = (new SelectQueryBuilder())
            ->from('t1')
            ->where('c1', '=', 'foo')
            ->where('c2', 'IS', null)
            ->build();
        $this->assertSame('SELECT * FROM t1 WHERE ((c1 = ?) AND (c2 IS NULL))', $sql);
        $this->assertSame(['foo'], $binds);

        list ($sql, $binds) = (new SelectQueryBuilder())
            ->from('t1')
            ->where('c1', '=', 'var_dump')
            ->where('c2', '=', 'var_dump')
            ->build();
        $this->assertSame('SELECT * FROM t1 WHERE ((c1 = ?) AND (c2 = ?))', $sql);
        $this->assertSame(['var_dump', 'var_dump'], $binds);

        list ($sql, $binds) = (new SelectQueryBuilder())
            ->from('t1')
            ->where('c1', '=', 'foo')
            ->where('c2', 'IS', null)
            ->build();
        $this->assertSame('SELECT * FROM t1 WHERE ((c1 = ?) AND (c2 IS NULL))', $sql);
        $this->assertSame(['foo'], $binds);

        list ($sql, $binds) = (new SelectQueryBuilder())
            ->from('t1')
            ->where('c1', '!=', 'foo')
            ->where('c2', '<>', 'bar')
            ->where('c3', 'IS NOT', null)
            ->build();
        $this->assertSame('SELECT * FROM t1 WHERE (((c1 != ?) AND (c2 <> ?)) AND (c3 IS NOT NULL))', $sql);
        $this->assertSame(['foo', 'bar'], $binds);

        $q = (new SelectQueryBuilder())->select('c1')->from('t2')->where('c2', '=', 'foo')->limit(1);
        list ($sql, $binds) = (new SelectQueryBuilder())
            ->from('t1')
            ->where(Creteria::value($q)->eq('bar'))
            ->build();
        $this->assertSame('SELECT * FROM t1 WHERE ((SELECT c1 FROM t2 WHERE (c2 = ?) LIMIT ?) = ?)', $sql);
        $this->assertSame(['foo', 1, 'bar'], $binds);

        list ($sql, $binds) = (new SelectQueryBuilder())
            ->from('t1')
            ->where('c1', '=', $q)
            ->build();
        $this->assertSame('SELECT * FROM t1 WHERE (c1 = (SELECT c1 FROM t2 WHERE (c2 = ?) LIMIT ?))', $sql);
        $this->assertSame(['foo', 1], $binds);
    }

    public function testWhereComparing()
    {
        list ($sql, $binds) = (new SelectQueryBuilder())
            ->from('t1')
            ->where('c1', '>', 0)
            ->where('c2', '<', 1)
            ->where('c3', '>=', 0)
            ->where('c4', '<=', 1)
            ->build();
        $this->assertSame('SELECT * FROM t1 WHERE ((((c1 > ?) AND (c2 < ?)) AND (c3 >= ?)) AND (c4 <= ?))', $sql);
        $this->assertSame([0, 1, 0, 1], $binds);
    }

    public function testWhereLike()
    {
        list ($sql, $binds) = (new SelectQueryBuilder())
            ->from('t1')
            ->where('c1', 'LIKE', '%foo%')
            ->where('c2', 'NOT LIKE', '%bar%')
            ->build();
        $this->assertSame('SELECT * FROM t1 WHERE ((c1 LIKE ?) AND (c2 NOT LIKE ?))', $sql);
        $this->assertSame(['%foo%', '%bar%'], $binds);
    }

    public function testWhereBetween()
    {
        list ($sql, $binds) = (new SelectQueryBuilder())
            ->from('t1')
            ->where('c1', 'BETWEEN', [1, 10])
            ->where('c2', 'NOT BETWEEN', [2, 20])
            ->build();
        $this->assertSame('SELECT * FROM t1 WHERE ((c1 BETWEEN ? AND ?) AND (c2 NOT BETWEEN ? AND ?))', $sql);
        $this->assertSame([1, 10, 2, 20], $binds);

        $q = (new SelectQueryBuilder())->select('c1')->from('t2')->where('c2', '=', 'foo');
        list ($sql, $binds) = (new SelectQueryBuilder())
            ->from('t1')
            ->where(Creteria::value($q)->between(1, 10))
            ->build();
        $this->assertSame('SELECT * FROM t1 WHERE ((SELECT c1 FROM t2 WHERE (c2 = ?)) BETWEEN ? AND ?)', $sql);
        $this->assertSame(['foo', 1, 10], $binds);
    }

    public function testWhereIn()
    {
        list ($sql, $binds) = (new SelectQueryBuilder())
            ->from('t1')
            ->where('c1', 'IN', [1, 2, 3])
            ->where('c2', 'NOT IN', [10, 20, 30])
            ->build();
        $this->assertSame('SELECT * FROM t1 WHERE ((c1 IN (?, ?, ?)) AND (c2 NOT IN (?, ?, ?)))', $sql);
        $this->assertSame([1, 2, 3, 10, 20, 30], $binds);

        $q = (new SelectQueryBuilder())->select('c1')->from('t2')->where('c2', '=', 'foo');
        list ($sql, $binds) = (new SelectQueryBuilder())
            ->from('t1')
            ->where('c1', 'IN', $q)
            ->build();
        $this->assertSame('SELECT * FROM t1 WHERE (c1 IN (SELECT c1 FROM t2 WHERE (c2 = ?)))', $sql);
        $this->assertSame(['foo'], $binds);

        $q = (new SelectQueryBuilder())->select('c1')->from('t2')->where('c2', '=', 'foo')->limit(1);
        list ($sql, $binds) = (new SelectQueryBuilder())
            ->from('t1')
            ->where(Creteria::value($q)->in([1, 2, 3]))
            ->build();
        $this->assertSame('SELECT * FROM t1 WHERE ((SELECT c1 FROM t2 WHERE (c2 = ?) LIMIT ?) IN (?, ?, ?))', $sql);
        $this->assertSame(['foo', 1, 1, 2, 3], $binds);
    }

    public function testWhereExpr()
    {
        list ($sql, $binds) = (new SelectQueryBuilder())
            ->from('t1')
            ->where(Creteria::raw('(c1 = ?)', ['hoge']))
            ->where(Creteria::raw('(c2 = ? OR c3 = ?)', [1, 2]))
            ->build();
        $this->assertSame('SELECT * FROM t1 WHERE ((c1 = ?) AND (c2 = ? OR c3 = ?))', $sql);
        $this->assertSame(['hoge', 1, 2], $binds);
    }

    public function testWhereExists()
    {
        $q = (new SelectQueryBuilder())->select('c1')->from('t2')->where('c2', '=', 'foo')->limit(1);
        list ($sql, $binds) = (new SelectQueryBuilder())
            ->from('t1')
            ->where('EXISTS', $q)
            ->build();
        $this->assertSame('SELECT * FROM t1 WHERE (EXISTS (SELECT c1 FROM t2 WHERE (c2 = ?) LIMIT ?))', $sql);
        $this->assertSame(['foo', 1], $binds);

        list ($sql, $binds) = (new SelectQueryBuilder())
            ->from('t1')
            ->where('NOT EXISTS', $q)
            ->build();
        $this->assertSame('SELECT * FROM t1 WHERE (NOT EXISTS (SELECT c1 FROM t2 WHERE (c2 = ?) LIMIT ?))', $sql);
        $this->assertSame(['foo', 1], $binds);
    }

    public function testWhereIsNull()
    {
        $q = (new SelectQueryBuilder())->select('c1')->from('t2')->where('c2', '=', 'foo')->limit(1);
        list ($sql, $binds) = (new SelectQueryBuilder())
            ->from('t1')
            ->where(Creteria::value($q)->isNull())
            ->build();
        $this->assertSame('SELECT * FROM t1 WHERE ((SELECT c1 FROM t2 WHERE (c2 = ?) LIMIT ?) IS NULL)', $sql);
        $this->assertSame(['foo', 1], $binds);

        $q = (new SelectQueryBuilder())->select('c1')->from('t2')->where('c2', '=', 'foo')->limit(1);
        list ($sql, $binds) = (new SelectQueryBuilder())
            ->from('t1')
            ->where(Creteria::value($q)->isNotNull())
            ->build();
        $this->assertSame('SELECT * FROM t1 WHERE ((SELECT c1 FROM t2 WHERE (c2 = ?) LIMIT ?) IS NOT NULL)', $sql);
        $this->assertSame(['foo', 1], $binds);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testWhereEmptyCondition()
    {
        (new SelectQueryBuilder())
            ->from('t1')
            ->where()
            ->build();
    }

    /**
     * @expectedException  InvalidArgumentException
     */
    public function testWhereInvalidOperator()
    {
        (new SelectQueryBuilder())
            ->from('t1')
            ->where('c1', '==', 'foo')
            ->build();
    }

    public function testGroupBy()
    {
        list ($sql, $binds) = (new SelectQueryBuilder())
            ->from('t1')
            ->groupBy('c1')
            ->build();
        $this->assertSame('SELECT * FROM t1 GROUP BY c1', $sql);
        $this->assertSame([], $binds);

        list ($sql, $binds) = (new SelectQueryBuilder())
            ->from('t1')
            ->groupBy(Creteria::raw('c1 + ?', [1]), 'DESC')
            ->build();
        $this->assertSame('SELECT * FROM t1 GROUP BY c1 + ? DESC', $sql);
        $this->assertSame([1], $binds);

        list ($sql, $binds) = (new SelectQueryBuilder())
            ->from('t1')
            ->groupBy('c1')
            ->groupBy('c2')
            ->build();
        $this->assertSame('SELECT * FROM t1 GROUP BY c1, c2', $sql);
        $this->assertSame([], $binds);

        $q = (new SelectQueryBuilder())->select('c1')->from('t2')->limit(1);
        list ($sql, $binds) = (new SelectQueryBuilder())
            ->from('t1')
            ->groupBy($q, 'DESC')
            ->build();
        $this->assertSame('SELECT * FROM t1 GROUP BY (SELECT c1 FROM t2 LIMIT ?) DESC', $sql);
        $this->assertSame([1], $binds);
	}

    public function testHaving()
    {
        list ($sql, $binds) = (new SelectQueryBuilder())
            ->from('t1')
            ->groupBy('c1')
            ->having('c2', '=', 'foo')
            ->having('c3', '=', 'bar')
            ->having('c4', 'IS', null)
            ->build();
        $this->assertSame('SELECT * FROM t1 GROUP BY c1 HAVING (((c2 = ?) AND (c3 = ?)) AND (c4 IS NULL))', $sql);
        $this->assertSame(['foo', 'bar'], $binds);

        list ($sql, $binds) = (new SelectQueryBuilder())
            ->from('t1')
            ->groupBy('c1')
            ->having('c2', '!=', 'foo')
            ->having('c3', '<>', 'bar')
            ->having('c4', 'IS NOT', null)
            ->build();
        $this->assertSame('SELECT * FROM t1 GROUP BY c1 HAVING (((c2 != ?) AND (c3 <> ?)) AND (c4 IS NOT NULL))', $sql, '値の不一致');
        $this->assertSame(['foo', 'bar'], $binds);
    }

    public function testOrderBy()
    {
        list ($sql, $binds) = (new SelectQueryBuilder())
            ->from('t1')
            ->orderBy('c1')
            ->build();
        $this->assertSame('SELECT * FROM t1 ORDER BY c1', $sql);
        $this->assertSame([], $binds);

        list ($sql, $binds) = (new SelectQueryBuilder())
            ->from('t1')
            ->orderBy(null)
            ->build();
        $this->assertSame('SELECT * FROM t1 ORDER BY NULL', $sql);
        $this->assertSame([], $binds);

        list ($sql, $binds) = (new SelectQueryBuilder())
            ->from('t1')
            ->orderBy(Creteria::raw('c1 + ?', [1]), 'DESC')
            ->build();
        $this->assertSame('SELECT * FROM t1 ORDER BY c1 + ? DESC', $sql);
        $this->assertSame([1], $binds);

        $q = (new SelectQueryBuilder())->select('c1')->from('t2')->limit(1);
        list ($sql, $binds) = (new SelectQueryBuilder())
            ->from('t1')
            ->orderBy($q, 'DESC')
            ->build();
        $this->assertSame('SELECT * FROM t1 ORDER BY (SELECT c1 FROM t2 LIMIT ?) DESC', $sql);
        $this->assertSame([1], $binds);
    }

    public function testLimit()
    {
        list ($sql, $binds) = (new SelectQueryBuilder())
            ->from('t1')
            ->limit(10)
            ->build();
        $this->assertSame('SELECT * FROM t1 LIMIT ?', $sql, 'LIMIT');
        $this->assertSame([10], $binds);
    }

    public function testOffset()
    {
        list ($sql, $binds) = (new SelectQueryBuilder())
            ->from('t1')
            ->limit(10)
            ->offset(10)
            ->build();
        $this->assertSame('SELECT * FROM t1 LIMIT ? OFFSET ?', $sql, 'OFFSET');
        $this->assertSame([10, 10], $binds);
    }

    public function testSuffix()
    {
        list ($sql, $binds) = (new SelectQueryBuilder())
            ->from('t1')
            ->suffix('FOR UPDATE')
            ->build();
        $this->assertSame('SELECT * FROM t1 FOR UPDATE', $sql);
        $this->assertSame([], $binds);
    }

    public function testForUpdate()
    {
        list ($sql, $binds) = (new SelectQueryBuilder())
            ->from('t1')
            ->forUpdate()
            ->build();
        $this->assertSame('SELECT * FROM t1 FOR UPDATE', $sql);
        $this->assertSame([], $binds);
    }

    public function testUnion()
    {
        $q1 = (new SelectQueryBuilder())->select('c1')->from('t1')->where('c1', '=', 'foo');
        $q2 = (new SelectQueryBuilder())->select('c1')->from('t1')->where('c1', '=', 'bar');

        list ($sql, $binds) = $q1->union($q2)->build();
        $this->assertSame('(SELECT c1 FROM t1 WHERE (c1 = ?)) UNION (SELECT c1 FROM t1 WHERE (c1 = ?))', $sql);
        $this->assertSame(['foo', 'bar'], $binds);
    }

    public function testUnionAll()
    {
        $q1 = (new SelectQueryBuilder())->select('c1')->from('t1')->where('c1', '=', 'foo');
        $q2 = (new SelectQueryBuilder())->select('c1')->from('t1')->where('c1', '=', 'bar');
        $q3 = (new SelectQueryBuilder())->select('c1')->from('t1')->where('c1', '=', 'baz');

        list ($sql, $binds) = $q1->unionAll($q2)->unionAll($q3)->build();
        $this->assertSame('(SELECT c1 FROM t1 WHERE (c1 = ?)) UNION ALL (SELECT c1 FROM t1 WHERE (c1 = ?)) UNION ALL (SELECT c1 FROM t1 WHERE (c1 = ?))', $sql);
        $this->assertSame(['foo', 'bar', 'baz'], $binds);
    }

    public function test__toString()
    {
        $q = (new SelectQueryBuilder())
            ->from('t1')
            ->where('c1', 'IN', [1, 2, 3])
            ->where('c2', '=', '\'foo\'')
            ->where('c3', 'IS NOT', null)
            ->where('c4', '=', true)
            ->where('c5', '=', false)
            ->orderBy('c1');
        $expected = "SELECT * FROM t1 WHERE (((((c1 IN (1, 2, 3)) AND (c2 = '\\'foo\\'')) AND (c3 IS NOT NULL)) AND (c4 = 1)) AND (c5 = 0)) ORDER BY c1";
        $this->assertSame($expected, (string) $q);

        $q = (new SelectQueryBuilder())
            ->from('t1')
            ->where('c1', '=', hex2bin('ff'));
        $expected = "SELECT * FROM t1 WHERE (c1 = x'ff')";
        $this->assertSame($expected, (string) $q);
    }
}
