<?php

namespace Emonkak\Orm\Tests;

use Emonkak\Database\PDOInterface;
use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\Grammar\DefaultGrammar;
use Emonkak\Orm\Pagination\Paginator;
use Emonkak\Orm\SelectBuilder;
use Emonkak\Orm\Sql;

/**
 * @covers Emonkak\Orm\SelectBuilder
 */
class SelectBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testGrammar()
    {
        $builder = (new SelectBuilder());
        $this->assertEquals(DefaultGrammar::getInstance(), $builder->getGrammar());
    }

    public function testSelect()
    {
        $query = (new SelectBuilder())
            ->from('t1')
            ->build();
        $this->assertEquals('SELECT * FROM t1', $query->getSql());
        $this->assertEquals([], $query->getBindings());

        $query = (new SelectBuilder())
            ->select('1')
            ->build();
        $this->assertEquals('SELECT 1', $query->getSql());
        $this->assertEquals([], $query->getBindings());

        $query = (new SelectBuilder())
            ->select('c1')
            ->select('c2')
            ->from('t1')
            ->build();
        $this->assertEquals('SELECT c1, c2 FROM t1', $query->getSql());
        $this->assertEquals([], $query->getBindings());

        $query = (new SelectBuilder())
            ->select('c1', 'a1')
            ->select('c2', 'a2')
            ->from('t1')
            ->build();
        $this->assertEquals('SELECT c1 AS a1, c2 AS a2 FROM t1', $query->getSql());
        $this->assertEquals([], $query->getBindings());

        $query = (new SelectBuilder())
            ->select(new Sql('? + 1', [100]), 'c1')
            ->from('t1')
            ->build();
        $this->assertEquals('SELECT ? + 1 AS c1 FROM t1', $query->getSql());
        $this->assertEquals([100], $query->getBindings());

        $builder = (new SelectBuilder())->from('t2')->where('c1', '=', 'foo');
        $query = (new SelectBuilder())
            ->select($builder, 'c1')
            ->from('t1')
            ->build();
        $this->assertEquals('SELECT (SELECT * FROM t2 WHERE (c1 = ?)) AS c1 FROM t1', $query->getSql());
        $this->assertEquals(['foo'], $query->getBindings());
    }

    public function testSelectAll()
    {
        $query = (new SelectBuilder())
            ->selectAll([
                'c1',
                'c2' => '1',
                'c3' => new Sql('? + 1', [100]),
            ])
            ->from('t1')
            ->build();
        $this->assertEquals('SELECT c1, 1 AS c2, ? + 1 AS c3 FROM t1', $query->getSql());
        $this->assertEquals([100], $query->getBindings());
    }

    public function testPrefix()
    {
        $query = (new SelectBuilder())
            ->prefix('SELECT SQL_CALC_FOUND_ROWS')
            ->from('t1')
            ->build();
        $this->assertEquals('SELECT SQL_CALC_FOUND_ROWS * FROM t1', $query->getSql());
        $this->assertEquals([], $query->getBindings());
    }

    public function testFrom()
    {
        $query = (new SelectBuilder())
            ->select('c1')
            ->from('t1', 'a1')
            ->build();
        $this->assertEquals('SELECT c1 FROM t1 AS a1', $query->getSql());
        $this->assertEquals([], $query->getBindings());

        $query = (new SelectBuilder())
            ->from('t1')
            ->from('t2')
            ->build();
        $this->assertEquals('SELECT * FROM t1, t2', $query->getSql());
        $this->assertEquals([], $query->getBindings());

        $builder = (new SelectBuilder())->from('t1');
        $query = (new SelectBuilder())
            ->from($builder, 'a1')
            ->build();
        $this->assertEquals('SELECT * FROM (SELECT * FROM t1) AS a1', $query->getSql());
        $this->assertEquals([], $query->getBindings());
    }

    public function testJoin()
    {
        $query = (new SelectBuilder())
            ->from('t1')
            ->join('t2', 't1.id = t2.id')
            ->join('t3', 't2.id = t3.id')
            ->build();
        $this->assertEquals('SELECT * FROM t1 JOIN t2 ON t1.id = t2.id JOIN t3 ON t2.id = t3.id', $query->getSql());
        $this->assertEquals([], $query->getBindings());

        $query = (new SelectBuilder())
            ->from('t1')
            ->outerJoin('t2', 't1.id = t2.id')
            ->join('t3', 't2.id = t3.id', null, 'INNER JOIN')
            ->build();
        $this->assertEquals('SELECT * FROM t1 LEFT OUTER JOIN t2 ON t1.id = t2.id INNER JOIN t3 ON t2.id = t3.id', $query->getSql());
        $this->assertEquals([], $query->getBindings());

        $builder = (new SelectBuilder())->from('t2');
        $query = (new SelectBuilder())
            ->from('t1')
            ->join($builder, 't1.id = t2.id', 't2')
            ->build();
        $this->assertEquals('SELECT * FROM t1 JOIN (SELECT * FROM t2) AS t2 ON t1.id = t2.id', $query->getSql());
        $this->assertEquals([], $query->getBindings());
    }

    public function testWhereEqual()
    {
        $query = (new SelectBuilder())
            ->from('t1')
            ->where('c1', '=', 'foo')
            ->where('c2', 'IS NULL')
            ->build();
        $this->assertEquals('SELECT * FROM t1 WHERE ((c1 = ?) AND (c2 IS NULL))', $query->getSql());
        $this->assertEquals(['foo'], $query->getBindings());

        $query = (new SelectBuilder())
            ->from('t1')
            ->where('c1', '=', 'var_dump')
            ->where('c2', '=', 'var_dump')
            ->build();
        $this->assertEquals('SELECT * FROM t1 WHERE ((c1 = ?) AND (c2 = ?))', $query->getSql());
        $this->assertEquals(['var_dump', 'var_dump'], $query->getBindings());

        $query = (new SelectBuilder())
            ->from('t1')
            ->where('c1', '=', 'foo')
            ->where('c2', 'IS NULL')
            ->build();
        $this->assertEquals('SELECT * FROM t1 WHERE ((c1 = ?) AND (c2 IS NULL))', $query->getSql());
        $this->assertEquals(['foo'], $query->getBindings());

        $query = (new SelectBuilder())
            ->from('t1')
            ->where('c1', '!=', 'foo')
            ->where('c2', '<>', 'bar')
            ->where('c3', 'IS NOT NULL')
            ->build();
        $this->assertEquals('SELECT * FROM t1 WHERE (((c1 != ?) AND (c2 <> ?)) AND (c3 IS NOT NULL))', $query->getSql());
        $this->assertEquals(['foo', 'bar'], $query->getBindings());

        $builder = (new SelectBuilder())->select('c1')->from('t2')->where('c2', '=', 'foo')->limit(1);
        $query = (new SelectBuilder())
            ->from('t1')
            ->where($builder, '=', 'bar')
            ->build();
        $this->assertEquals('SELECT * FROM t1 WHERE ((SELECT c1 FROM t2 WHERE (c2 = ?) LIMIT ?) = ?)', $query->getSql());
        $this->assertEquals(['foo', 1, 'bar'], $query->getBindings());

        $query = (new SelectBuilder())
            ->from('t1')
            ->where('c1', '=', $builder)
            ->build();
        $this->assertEquals('SELECT * FROM t1 WHERE (c1 = (SELECT c1 FROM t2 WHERE (c2 = ?) LIMIT ?))', $query->getSql());
        $this->assertEquals(['foo', 1], $query->getBindings());
    }

    public function testWhereComparing()
    {
        $query = (new SelectBuilder())
            ->from('t1')
            ->where('c1', '>', 0)
            ->where('c2', '<', 1)
            ->where('c3', '>=', 0)
            ->where('c4', '<=', 1)
            ->build();
        $this->assertEquals('SELECT * FROM t1 WHERE ((((c1 > ?) AND (c2 < ?)) AND (c3 >= ?)) AND (c4 <= ?))', $query->getSql());
        $this->assertEquals([0, 1, 0, 1], $query->getBindings());
    }

    public function testWhereLike()
    {
        $query = (new SelectBuilder())
            ->from('t1')
            ->where('c1', 'LIKE', '%foo%')
            ->where('c2', 'NOT LIKE', '%bar%')
            ->build();
        $this->assertEquals('SELECT * FROM t1 WHERE ((c1 LIKE ?) AND (c2 NOT LIKE ?))', $query->getSql());
        $this->assertEquals(['%foo%', '%bar%'], $query->getBindings());
    }

    public function testWhereBetween()
    {
        $query = (new SelectBuilder())
            ->from('t1')
            ->where('c1', 'BETWEEN', 1, 10)
            ->where('c2', 'NOT BETWEEN', 2, 20)
            ->build();
        $this->assertEquals('SELECT * FROM t1 WHERE ((c1 BETWEEN ? AND ?) AND (c2 NOT BETWEEN ? AND ?))', $query->getSql());
        $this->assertEquals([1, 10, 2, 20], $query->getBindings());

        $builder = (new SelectBuilder())->select('c1')->from('t2')->where('c2', '=', 'foo');
        $query = (new SelectBuilder())
            ->from('t1')
            ->where($builder, 'BETWEEN', 1, 10)
            ->build();
        $this->assertEquals('SELECT * FROM t1 WHERE ((SELECT c1 FROM t2 WHERE (c2 = ?)) BETWEEN ? AND ?)', $query->getSql());
        $this->assertEquals(['foo', 1, 10], $query->getBindings());
    }

    public function testWhereIn()
    {
        $query = (new SelectBuilder())
            ->from('t1')
            ->where('c1', 'IN', [1, 2, 3])
            ->where('c2', 'NOT IN', [10, 20, 30])
            ->build();
        $this->assertEquals('SELECT * FROM t1 WHERE ((c1 IN (?, ?, ?)) AND (c2 NOT IN (?, ?, ?)))', $query->getSql());
        $this->assertEquals([1, 2, 3, 10, 20, 30], $query->getBindings());

        $builder = (new SelectBuilder())->select('c1')->from('t2')->where('c2', '=', 'foo');
        $query = (new SelectBuilder())
            ->from('t1')
            ->where('c1', 'IN', $builder)
            ->build();
        $this->assertEquals('SELECT * FROM t1 WHERE (c1 IN (SELECT c1 FROM t2 WHERE (c2 = ?)))', $query->getSql());
        $this->assertEquals(['foo'], $query->getBindings());

        $builder = (new SelectBuilder())->select('c1')->from('t2')->where('c2', '=', 'foo')->limit(1);
        $query = (new SelectBuilder())
            ->from('t1')
            ->where($builder, 'IN', [1, 2, 3])
            ->build();
        $this->assertEquals('SELECT * FROM t1 WHERE ((SELECT c1 FROM t2 WHERE (c2 = ?) LIMIT ?) IN (?, ?, ?))', $query->getSql());
        $this->assertEquals(['foo', 1, 1, 2, 3], $query->getBindings());
    }

    public function testWhereSql()
    {
        $query = (new SelectBuilder())
            ->from('t1')
            ->where(new Sql('(c1 = ?)', ['hoge']))
            ->where(new Sql('(c2 = ? OR c3 = ?)', [1, 2]))
            ->build();
        $this->assertEquals('SELECT * FROM t1 WHERE ((c1 = ?) AND (c2 = ? OR c3 = ?))', $query->getSql());
        $this->assertEquals(['hoge', 1, 2], $query->getBindings());
    }

    public function testWhereExists()
    {
        $builder = (new SelectBuilder())->select('c1')->from('t2')->where('c2', '=', 'foo')->limit(1);
        $query = (new SelectBuilder())
            ->from('t1')
            ->where($builder, 'EXISTS')
            ->build();
        $this->assertEquals('SELECT * FROM t1 WHERE (EXISTS (SELECT c1 FROM t2 WHERE (c2 = ?) LIMIT ?))', $query->getSql());
        $this->assertEquals(['foo', 1], $query->getBindings());

        $query = (new SelectBuilder())
            ->from('t1')
            ->where($builder, 'NOT EXISTS')
            ->build();
        $this->assertEquals('SELECT * FROM t1 WHERE (NOT EXISTS (SELECT c1 FROM t2 WHERE (c2 = ?) LIMIT ?))', $query->getSql());
        $this->assertEquals(['foo', 1], $query->getBindings());
    }

    public function testWhereIsNull()
    {
        $builder = (new SelectBuilder())->select('c1')->from('t2')->where('c2', '=', 'foo')->limit(1);
        $query = (new SelectBuilder())
            ->from('t1')
            ->where($builder, 'IS NULL')
            ->build();
        $this->assertEquals('SELECT * FROM t1 WHERE ((SELECT c1 FROM t2 WHERE (c2 = ?) LIMIT ?) IS NULL)', $query->getSql());
        $this->assertEquals(['foo', 1], $query->getBindings());

        $builder = (new SelectBuilder())->select('c1')->from('t2')->where('c2', '=', 'foo')->limit(1);
        $query = (new SelectBuilder())
            ->from('t1')
            ->where($builder, 'IS NOT NULL')
            ->build();
        $this->assertEquals('SELECT * FROM t1 WHERE ((SELECT c1 FROM t2 WHERE (c2 = ?) LIMIT ?) IS NOT NULL)', $query->getSql());
        $this->assertEquals(['foo', 1], $query->getBindings());
    }

    /**
     * @expectedException UnexpectedValueException
     */
    public function testWhereInvalidOperator()
    {
        (new SelectBuilder())
            ->from('t1')
            ->where('c1', '==', 'foo')
            ->build();
    }

    public function testOrWhere()
    {
        $query = (new SelectBuilder())
            ->from('t1')
            ->where('c1', '=', 'foo')
            ->orWhere('c2', '=', 'bar')
            ->build();
        $this->assertEquals('SELECT * FROM t1 WHERE ((c1 = ?) OR (c2 = ?))', $query->getSql());
        $this->assertEquals(['foo', 'bar'], $query->getBindings());
    }

    public function testGroupWhere()
    {
        $query = (new SelectBuilder())
            ->from('t1')
            ->where('c1', '=', 'foo')
            ->groupWhere(function($builder) {
                return $builder
                    ->where('c2', '=', 'bar')
                    ->orWhere('c3', '=', 'baz');
            })
            ->build();
        $this->assertEquals('SELECT * FROM t1 WHERE ((c1 = ?) AND ((c2 = ?) OR (c3 = ?)))', $query->getSql());
        $this->assertEquals(['foo', 'bar', 'baz'], $query->getBindings());

        $query = (new SelectBuilder())
            ->from('t1')
            ->where('c1', '=', 'foo')
            ->groupWhere(function($builder) {
                return $builder;
            })
            ->build();
        $this->assertEquals('SELECT * FROM t1 WHERE (c1 = ?)', $query->getSql());
        $this->assertEquals(['foo'], $query->getBindings());
    }

    public function testOrGroupWhere()
    {
        $query = (new SelectBuilder())
            ->from('t1')
            ->where('c1', '=', 'foo')
            ->orGroupWhere(function($builder) {
                return $builder
                    ->where('c2', '=', 'bar')
                    ->where('c3', '=', 'baz');
            })
            ->build();
        $this->assertEquals('SELECT * FROM t1 WHERE ((c1 = ?) OR ((c2 = ?) AND (c3 = ?)))', $query->getSql());
        $this->assertEquals(['foo', 'bar', 'baz'], $query->getBindings());

        $query = (new SelectBuilder())
            ->from('t1')
            ->where('c1', '=', 'foo')
            ->orGroupWhere(function($builder) {
                return $builder;
            })
            ->build();
        $this->assertEquals('SELECT * FROM t1 WHERE (c1 = ?)', $query->getSql());
        $this->assertEquals(['foo'], $query->getBindings());
    }

    public function testGroupBy()
    {
        $query = (new SelectBuilder())
            ->from('t1')
            ->groupBy('c1')
            ->build();
        $this->assertEquals('SELECT * FROM t1 GROUP BY c1', $query->getSql());
        $this->assertEquals([], $query->getBindings());

        $query = (new SelectBuilder())
            ->from('t1')
            ->groupBy(new Sql('c1 + ?', [1]), 'DESC')
            ->build();
        $this->assertEquals('SELECT * FROM t1 GROUP BY c1 + ? DESC', $query->getSql());
        $this->assertEquals([1], $query->getBindings());

        $query = (new SelectBuilder())
            ->from('t1')
            ->groupBy('c1')
            ->groupBy('c2')
            ->build();
        $this->assertEquals('SELECT * FROM t1 GROUP BY c1, c2', $query->getSql());
        $this->assertEquals([], $query->getBindings());

        $builder = (new SelectBuilder())->select('c1')->from('t2')->limit(1);
        $query = (new SelectBuilder())
            ->from('t1')
            ->groupBy($builder, 'DESC')
            ->build();
        $this->assertEquals('SELECT * FROM t1 GROUP BY (SELECT c1 FROM t2 LIMIT ?) DESC', $query->getSql());
        $this->assertEquals([1], $query->getBindings());
    }

    public function testHaving()
    {
        $query = (new SelectBuilder())
            ->from('t1')
            ->groupBy('c1')
            ->having('c2', '=', 'foo')
            ->having('c3', '=', 'bar')
            ->having('c4', 'IS NULL')
            ->build();
        $this->assertEquals('SELECT * FROM t1 GROUP BY c1 HAVING (((c2 = ?) AND (c3 = ?)) AND (c4 IS NULL))', $query->getSql());
        $this->assertEquals(['foo', 'bar'], $query->getBindings());

        $query = (new SelectBuilder())
            ->from('t1')
            ->groupBy('c1')
            ->having('c2', '!=', 'foo')
            ->having('c3', '<>', 'bar')
            ->having('c4', 'IS NOT NULL')
            ->build();
        $this->assertEquals('SELECT * FROM t1 GROUP BY c1 HAVING (((c2 != ?) AND (c3 <> ?)) AND (c4 IS NOT NULL))', $query->getSql());
        $this->assertEquals(['foo', 'bar'], $query->getBindings());
    }

    public function testGroupHaving()
    {
        $query = (new SelectBuilder())
            ->from('t1')
            ->groupBy('c1')
            ->having('c1', '=', 'foo')
            ->groupHaving(function($builder) {
                return $builder
                    ->having('c2', '=', 'bar')
                    ->orHaving('c3', '=', 'baz');
            })
            ->build();
        $this->assertEquals('SELECT * FROM t1 GROUP BY c1 HAVING ((c1 = ?) AND ((c2 = ?) OR (c3 = ?)))', $query->getSql());
        $this->assertEquals(['foo', 'bar', 'baz'], $query->getBindings());

        $query = (new SelectBuilder())
            ->from('t1')
            ->groupBy('c1')
            ->having('c1', '=', 'foo')
            ->groupHaving(function($builder) {
                return $builder;
            })
            ->build();
        $this->assertEquals('SELECT * FROM t1 GROUP BY c1 HAVING (c1 = ?)', $query->getSql());
        $this->assertEquals(['foo'], $query->getBindings());
    }

    public function testGroupOrHaving()
    {
        $query = (new SelectBuilder())
            ->from('t1')
            ->groupBy('c1')
            ->having('c1', '=', 'foo')
            ->orGroupHaving(function($builder) {
                return $builder
                    ->having('c2', '=', 'bar')
                    ->having('c3', '=', 'baz');
            })
            ->build();
        $this->assertEquals('SELECT * FROM t1 GROUP BY c1 HAVING ((c1 = ?) OR ((c2 = ?) AND (c3 = ?)))', $query->getSql());
        $this->assertEquals(['foo', 'bar', 'baz'], $query->getBindings());

        $query = (new SelectBuilder())
            ->from('t1')
            ->groupBy('c1')
            ->having('c1', '=', 'foo')
            ->orGroupHaving(function($builder) {
                return $builder;
            })
            ->build();
        $this->assertEquals('SELECT * FROM t1 GROUP BY c1 HAVING (c1 = ?)', $query->getSql());
        $this->assertEquals(['foo'], $query->getBindings());
    }

    public function testOrderBy()
    {
        $query = (new SelectBuilder())
            ->from('t1')
            ->orderBy('c1')
            ->build();
        $this->assertEquals('SELECT * FROM t1 ORDER BY c1', $query->getSql());
        $this->assertEquals([], $query->getBindings());

        $query = (new SelectBuilder())
            ->from('t1')
            ->orderBy('NULL')
            ->build();
        $this->assertEquals('SELECT * FROM t1 ORDER BY NULL', $query->getSql());
        $this->assertEquals([], $query->getBindings());

        $query = (new SelectBuilder())
            ->from('t1')
            ->orderBy(new Sql('c1 + ?', [1]), 'DESC')
            ->build();
        $this->assertEquals('SELECT * FROM t1 ORDER BY c1 + ? DESC', $query->getSql());
        $this->assertEquals([1], $query->getBindings());

        $builder = (new SelectBuilder())->select('c1')->from('t2')->limit(1);
        $query = (new SelectBuilder())
            ->from('t1')
            ->orderBy($builder, 'DESC')
            ->build();
        $this->assertEquals('SELECT * FROM t1 ORDER BY (SELECT c1 FROM t2 LIMIT ?) DESC', $query->getSql());
        $this->assertEquals([1], $query->getBindings());
    }

    public function testLimit()
    {
        $query = (new SelectBuilder())
            ->from('t1')
            ->limit(10)
            ->build();
        $this->assertEquals('SELECT * FROM t1 LIMIT ?', $query->getSql());
        $this->assertEquals([10], $query->getBindings());
    }

    public function testOffset()
    {
        $query = (new SelectBuilder())
            ->from('t1')
            ->limit(10)
            ->offset(10)
            ->build();
        $this->assertEquals('SELECT * FROM t1 LIMIT ? OFFSET ?', $query->getSql(), 'OFFSET');
        $this->assertEquals([10, 10], $query->getBindings());
    }

    public function testSuffix()
    {
        $query = (new SelectBuilder())
            ->from('t1')
            ->suffix('FOR UPDATE')
            ->build();
        $this->assertEquals('SELECT * FROM t1 FOR UPDATE', $query->getSql());
        $this->assertEquals([], $query->getBindings());
    }

    public function testForUpdate()
    {
        $query = (new SelectBuilder())
            ->from('t1')
            ->forUpdate()
            ->build();
        $this->assertEquals('SELECT * FROM t1 FOR UPDATE', $query->getSql());
        $this->assertEquals([], $query->getBindings());
    }

    public function testUnion()
    {
        $builder1 = (new SelectBuilder())->select('c1')->from('t1')->where('c1', '=', 'foo');
        $builder2 = (new SelectBuilder())->select('c1')->from('t1')->where('c1', '=', 'bar');

        $query = $builder1->union($builder2)->build();
        $this->assertEquals('(SELECT c1 FROM t1 WHERE (c1 = ?)) UNION (SELECT c1 FROM t1 WHERE (c1 = ?))', $query->getSql());
        $this->assertEquals(['foo', 'bar'], $query->getBindings());
    }

    public function testUnionAll()
    {
        $builder1 = (new SelectBuilder())->select('c1')->from('t1')->where('c1', '=', 'foo');
        $builder2 = (new SelectBuilder())->select('c1')->from('t1')->where('c1', '=', 'bar');
        $builder3 = (new SelectBuilder())->select('c1')->from('t1')->where('c1', '=', 'baz');

        $query = $builder1->unionAll($builder2)->unionAll($builder3)->build();
        $this->assertEquals('(SELECT c1 FROM t1 WHERE (c1 = ?)) UNION ALL (SELECT c1 FROM t1 WHERE (c1 = ?)) UNION ALL (SELECT c1 FROM t1 WHERE (c1 = ?))', $query->getSql());
        $this->assertEquals(['foo', 'bar', 'baz'], $query->getBindings());
    }

    public function testAggregate()
    {
        $stmt = $this->getMock(PDOStatementInterface::class);
        $stmt
            ->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $stmt
            ->expects($this->once())
            ->method('fetchColumn')
            ->willReturn(123);

        $pdo = $this->getMock(PDOInterface::class);
        $pdo
            ->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);

        $builder = (new SelectBuilder());
        $this->assertSame(123, $builder->aggregate($pdo, 'COUNT(*)'));
    }

    public function testPaginate()
    {
        $stmt = $this->getMock(PDOStatementInterface::class);
        $stmt
            ->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $stmt
            ->expects($this->once())
            ->method('fetchColumn')
            ->willReturn(1000);

        $pdo = $this->getMock(PDOInterface::class);
        $pdo
            ->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);

        $fetcher = $this->getMock(FetcherInterface::class);

        $paginator = (new SelectBuilder())->paginate($pdo, $fetcher, 100);
        $this->assertInstanceOf(Paginator::class, $paginator);
        $this->assertSame(100, $paginator->getPerPage());
        $this->assertSame(1000, $paginator->getNumItems());
    }
}
