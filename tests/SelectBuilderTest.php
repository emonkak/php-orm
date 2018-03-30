<?php

namespace Emonkak\Orm\Tests;

use Emonkak\Database\PDOInterface;
use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\Grammar\GrammarInterface;
use Emonkak\Orm\Pagination\Paginator;
use Emonkak\Orm\SelectBuilder;
use Emonkak\Orm\Sql;

/**
 * @covers Emonkak\Orm\SelectBuilder
 */
class SelectBuilderTest extends \PHPUnit_Framework_TestCase
{
    use QueryBuilderTestTrait;

    public function testGetGrammar()
    {
        $grammar = $this->createMock(GrammarInterface::class);
        $builder = new SelectBuilder($grammar);
        $this->assertSame($grammar, $builder->getGrammar());
    }

    public function testGetAccesors()
    {
        $unionBuilder = $this->createSelectBuilder()->select('c1')->from('t1');

        $builder = $this->createSelectBuilder()
            ->select('c1')
            ->from('t1')
            ->join('t2', 't1.id = t2.id')
            ->where('t1.c1', '=', 123)
            ->groupBy('t1.c1')
            ->orderBy('t1.c2')
            ->having('t1.c2', '=', 456)
            ->offset(12)
            ->limit(34)
            ->suffix('FOR UPDATE')
            ->union($unionBuilder);

        $this->assertSame('SELECT', $builder->getPrefix());
        $this->assertEquals([new Sql('c1')], $builder->getSelect());
        $this->assertEquals([new Sql('t1')], $builder->getFrom());
        $this->assertEquals([new Sql('JOIN t2 ON t1.id = t2.id', [])], $builder->getJoin());
        $this->assertQueryIs('(t1.c1 = ?)', [123], $builder->getWhere());
        $this->assertEquals([new Sql('t1.c1')], $builder->getGroupBy());
        $this->assertEquals([new Sql('t1.c2')], $builder->getOrderBy());
        $this->assertQueryIs('(t1.c2 = ?)', [456], $builder->getHaving());
        $this->assertSame(12, $builder->getOffset());
        $this->assertSame(34, $builder->getLimit());
        $this->assertSame('FOR UPDATE', $builder->getSuffix());
        $this->assertEquals([new Sql('UNION (SELECT c1 FROM t1)')], $builder->getUnion());
    }

    public function testSelect()
    {
        $query = $this->createSelectBuilder()
            ->from('t1')
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1',
            [],
            $query
        );

        $query = $this->createSelectBuilder()
            ->select('1')
            ->build();
        $this->assertQueryIs(
            'SELECT 1',
            [],
            $query
        );

        $query = $this->createSelectBuilder()
            ->select('c1')
            ->select('c2')
            ->from('t1')
            ->build();
        $this->assertQueryIs(
            'SELECT c1, c2 FROM t1',
            [],
            $query
        );

        $query = $this->createSelectBuilder()
            ->select('c1', 'a1')
            ->select('c2', 'a2')
            ->from('t1')
            ->build();
        $this->assertQueryIs(
            'SELECT c1 AS a1, c2 AS a2 FROM t1',
            [],
            $query
        );

        $query = $this->createSelectBuilder()
            ->select(new Sql('? + 1', [100]), 'c1')
            ->from('t1')
            ->build();
        $this->assertQueryIs(
            'SELECT ? + 1 AS c1 FROM t1',
            [100],
            $query
        );

        $builder = $this->createSelectBuilder()->from('t2')->where('c1', '=', 'foo');
        $query = $this->createSelectBuilder()
            ->select($builder, 'c1')
            ->from('t1')
            ->build();
        $this->assertQueryIs(
            'SELECT (SELECT * FROM t2 WHERE (c1 = ?)) AS c1 FROM t1',
            ['foo'],
            $query
        );
    }

    public function testSelectAll()
    {
        $query = $this->createSelectBuilder()
            ->selectAll([
                'c1',
                'c2' => '1',
                'c3' => new Sql('? + 1', [100]),
            ])
            ->from('t1')
            ->build();
        $this->assertQueryIs(
            'SELECT c1, 1 AS c2, ? + 1 AS c3 FROM t1',
            [100],
            $query
        );
    }

    public function testPrefix()
    {
        $query = $this->createSelectBuilder()
            ->prefix('SELECT SQL_CALC_FOUND_ROWS')
            ->from('t1')
            ->build();
        $this->assertQueryIs(
            'SELECT SQL_CALC_FOUND_ROWS * FROM t1',
            [],
            $query
        );
    }

    public function testFrom()
    {
        $query = $this->createSelectBuilder()
            ->select('c1')
            ->from('t1', 'a1')
            ->build();
        $this->assertQueryIs(
            'SELECT c1 FROM t1 AS a1',
            [],
            $query
        );

        $query = $this->createSelectBuilder()
            ->from('t1')
            ->from('t2')
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1, t2',
            [],
            $query
        );

        $builder = $this->createSelectBuilder()->from('t1');
        $query = $this->createSelectBuilder()
            ->from($builder, 'a1')
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM (SELECT * FROM t1) AS a1',
            [],
            $query
        );
    }

    public function testJoin()
    {
        $query = $this->createSelectBuilder()
            ->from('t1')
            ->join('t2', 't1.id = t2.id')
            ->join('t3', 't2.id = t3.id')
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 JOIN t2 ON t1.id = t2.id JOIN t3 ON t2.id = t3.id',
            [],
            $query
        );

        $query = $this->createSelectBuilder()
            ->from('t1')
            ->outerJoin('t2', 't1.id = t2.id')
            ->join('t3', 't2.id = t3.id', null, 'INNER JOIN')
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 LEFT OUTER JOIN t2 ON t1.id = t2.id INNER JOIN t3 ON t2.id = t3.id',
            [],
            $query
        );

        $builder = $this->createSelectBuilder()->from('t2');
        $query = $this->createSelectBuilder()
            ->from('t1')
            ->join($builder, 't1.id = t2.id', 't2')
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 JOIN (SELECT * FROM t2) AS t2 ON t1.id = t2.id',
            [],
            $query
        );
    }

    public function testWhereEqual()
    {
        $query = $this->createSelectBuilder()
            ->from('t1')
            ->where('c1', '=', 'foo')
            ->where('c2', 'IS', null)
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 WHERE ((c1 = ?) AND (c2 IS NULL))',
            ['foo'],
            $query
        );

        $query = $this->createSelectBuilder()
            ->from('t1')
            ->where('c1', '=', 'var_dump')
            ->where('c2', '=', 'var_dump')
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 WHERE ((c1 = ?) AND (c2 = ?))',
            ['var_dump', 'var_dump'],
            $query
        );

        $query = $this->createSelectBuilder()
            ->from('t1')
            ->where('c1', '=', 'foo')
            ->where('c2', 'IS', null)
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 WHERE ((c1 = ?) AND (c2 IS NULL))',
            ['foo'],
            $query
        );

        $query = $this->createSelectBuilder()
            ->from('t1')
            ->where('c1', '!=', 'foo')
            ->where('c2', '<>', 'bar')
            ->where('c3', 'IS NOT', null)
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 WHERE (((c1 != ?) AND (c2 <> ?)) AND (c3 IS NOT NULL))',
            ['foo', 'bar'],
            $query
        );

        $builder = $this->createSelectBuilder()->select('c1')->from('t2')->where('c2', '=', 'foo')->limit(1);
        $query = $this->createSelectBuilder()
            ->from('t1')
            ->where($builder, '=', 'bar')
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 WHERE ((SELECT c1 FROM t2 WHERE (c2 = ?) LIMIT ?) = ?)',
            ['foo', 1, 'bar'],
            $query
        );

        $query = $this->createSelectBuilder()
            ->from('t1')
            ->where('c1', '=', $builder)
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 WHERE (c1 = (SELECT c1 FROM t2 WHERE (c2 = ?) LIMIT ?))',
            ['foo', 1],
            $query
        );
    }

    public function testWhereComparing()
    {
        $query = $this->createSelectBuilder()
            ->from('t1')
            ->where('c1', '>', 0)
            ->where('c2', '<', 1)
            ->where('c3', '>=', 0)
            ->where('c4', '<=', 1)
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 WHERE ((((c1 > ?) AND (c2 < ?)) AND (c3 >= ?)) AND (c4 <= ?))',
            [0, 1, 0, 1],
            $query
        );
    }

    public function testWhereLike()
    {
        $query = $this->createSelectBuilder()
            ->from('t1')
            ->where('c1', 'LIKE', '%foo%')
            ->where('c2', 'NOT LIKE', '%bar%')
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 WHERE ((c1 LIKE ?) AND (c2 NOT LIKE ?))',
            ['%foo%', '%bar%'],
            $query
        );
    }

    public function testWhereBetween()
    {
        $query = $this->createSelectBuilder()
            ->from('t1')
            ->where('c1', 'BETWEEN', 1, 10)
            ->where('c2', 'NOT BETWEEN', 2, 20)
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 WHERE ((c1 BETWEEN ? AND ?) AND (c2 NOT BETWEEN ? AND ?))',
            [1, 10, 2, 20],
            $query
        );

        $builder = $this->createSelectBuilder()->select('c1')->from('t2')->where('c2', '=', 'foo');
        $query = $this->createSelectBuilder()
            ->from('t1')
            ->where($builder, 'BETWEEN', 1, 10)
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 WHERE ((SELECT c1 FROM t2 WHERE (c2 = ?)) BETWEEN ? AND ?)',
            ['foo', 1, 10],
            $query
        );
    }

    public function testWhereIn()
    {
        $query = $this->createSelectBuilder()
            ->from('t1')
            ->where('c1', 'IN', [1, 2, 3])
            ->where('c2', 'NOT IN', [10, 20, 30])
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 WHERE ((c1 IN (?, ?, ?)) AND (c2 NOT IN (?, ?, ?)))',
            [1, 2, 3, 10, 20, 30],
            $query
        );

        $builder = $this->createSelectBuilder()->select('c1')->from('t2')->where('c2', '=', 'foo');
        $query = $this->createSelectBuilder()
            ->from('t1')
            ->where('c1', 'IN', $builder)
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 WHERE (c1 IN (SELECT c1 FROM t2 WHERE (c2 = ?)))',
            ['foo'],
            $query
        );

        $builder = $this->createSelectBuilder()->select('c1')->from('t2')->where('c2', '=', 'foo')->limit(1);
        $query = $this->createSelectBuilder()
            ->from('t1')
            ->where($builder, 'IN', [1, 2, 3])
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 WHERE ((SELECT c1 FROM t2 WHERE (c2 = ?) LIMIT ?) IN (?, ?, ?))',
            ['foo', 1, 1, 2, 3],
            $query
        );
    }

    public function testWhereSql()
    {
        $query = $this->createSelectBuilder()
            ->from('t1')
            ->where(new Sql('(c1 = ?)', ['hoge']))
            ->where(new Sql('(c2 = ? OR c3 = ?)', [1, 2]))
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 WHERE ((c1 = ?) AND (c2 = ? OR c3 = ?))',
            ['hoge', 1, 2],
            $query
        );
    }

    public function testWhereExists()
    {
        $builder = $this->createSelectBuilder()->select('c1')->from('t2')->where('c2', '=', 'foo')->limit(1);
        $query = $this->createSelectBuilder()
            ->from('t1')
            ->where('EXISTS', $builder)
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 WHERE (EXISTS (SELECT c1 FROM t2 WHERE (c2 = ?) LIMIT ?))',
            ['foo', 1],
            $query
        );

        $query = $this->createSelectBuilder()
            ->from('t1')
            ->where('NOT EXISTS', $builder)
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 WHERE (NOT EXISTS (SELECT c1 FROM t2 WHERE (c2 = ?) LIMIT ?))',
            ['foo', 1],
            $query
        );
    }

    public function testWhereIsNull()
    {
        $builder = $this->createSelectBuilder()->select('c1')->from('t2')->where('c2', '=', 'foo')->limit(1);
        $query = $this->createSelectBuilder()
            ->from('t1')
            ->where($builder, 'IS', null)
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 WHERE ((SELECT c1 FROM t2 WHERE (c2 = ?) LIMIT ?) IS NULL)',
            ['foo', 1],
            $query
        );

        $builder = $this->createSelectBuilder()->select('c1')->from('t2')->where('c2', '=', 'foo')->limit(1);
        $query = $this->createSelectBuilder()
            ->from('t1')
            ->where($builder, 'IS NOT', null)
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 WHERE ((SELECT c1 FROM t2 WHERE (c2 = ?) LIMIT ?) IS NOT NULL)',
            ['foo', 1],
            $query
        );
    }

    public function testOrWhere()
    {
        $query = $this->createSelectBuilder()
            ->from('t1')
            ->where('c1', '=', 'foo')
            ->orWhere('c2', '=', 'bar')
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 WHERE ((c1 = ?) OR (c2 = ?))',
            ['foo', 'bar'],
            $query
        );
    }

    public function testGroupBy()
    {
        $query = $this->createSelectBuilder()
            ->from('t1')
            ->groupBy('c1')
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 GROUP BY c1',
            [],
            $query
        );

        $query = $this->createSelectBuilder()
            ->from('t1')
            ->groupBy(new Sql('c1 + ?', [1]), 'DESC')
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 GROUP BY c1 + ? DESC',
            [1],
            $query
        );

        $query = $this->createSelectBuilder()
            ->from('t1')
            ->groupBy('c1')
            ->groupBy('c2')
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 GROUP BY c1, c2',
            [],
            $query
        );

        $builder = $this->createSelectBuilder()->select('c1')->from('t2')->limit(1);
        $query = $this->createSelectBuilder()
            ->from('t1')
            ->groupBy($builder, 'DESC')
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 GROUP BY (SELECT c1 FROM t2 LIMIT ?) DESC',
            [1],
            $query
        );
    }

    public function testHaving()
    {
        $query = $this->createSelectBuilder()
            ->from('t1')
            ->groupBy('c1')
            ->having('c2', '=', 'foo')
            ->having('c3', '=', 'bar')
            ->having('c4', 'IS', null)
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 GROUP BY c1 HAVING (((c2 = ?) AND (c3 = ?)) AND (c4 IS NULL))',
            ['foo', 'bar'],
            $query
        );

        $query = $this->createSelectBuilder()
            ->from('t1')
            ->groupBy('c1')
            ->having('c2', '!=', 'foo')
            ->having('c3', '<>', 'bar')
            ->having('c4', 'IS NOT', NULL)
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 GROUP BY c1 HAVING (((c2 != ?) AND (c3 <> ?)) AND (c4 IS NOT NULL))',
            ['foo', 'bar'],
            $query
        );
    }

    public function testOrHaving()
    {
        $query = $this->createSelectBuilder()
            ->from('t1')
            ->groupBy('c1')
            ->having('c2', '=', 'foo')
            ->orHaving('c3', '=', 'bar')
            ->orHaving('c4', 'IS', null)
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 GROUP BY c1 HAVING (((c2 = ?) OR (c3 = ?)) OR (c4 IS NULL))',
            ['foo', 'bar'],
            $query
        );
    }

    public function testOrderBy()
    {
        $query = $this->createSelectBuilder()
            ->from('t1')
            ->orderBy('c1')
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 ORDER BY c1',
            [],
            $query
        );

        $query = $this->createSelectBuilder()
            ->from('t1')
            ->orderBy('NULL')
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 ORDER BY NULL',
            [],
            $query
        );

        $query = $this->createSelectBuilder()
            ->from('t1')
            ->orderBy(new Sql('c1 + ?', [1]), 'DESC')
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 ORDER BY c1 + ? DESC',
            [1],
            $query
        );

        $builder = $this->createSelectBuilder()->select('c1')->from('t2')->limit(1);
        $query = $this->createSelectBuilder()
            ->from('t1')
            ->orderBy($builder, 'DESC')
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 ORDER BY (SELECT c1 FROM t2 LIMIT ?) DESC',
            [1],
            $query
        );
    }

    public function testLimit()
    {
        $query = $this->createSelectBuilder()
            ->from('t1')
            ->limit(10)
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 LIMIT ?',
            [10],
            $query
        );
    }

    public function testOffset()
    {
        $query = $this->createSelectBuilder()
            ->from('t1')
            ->limit(10)
            ->offset(10)
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 LIMIT ? OFFSET ?',
            [10, 10],
            $query
        );
    }

    public function testSuffix()
    {
        $query = $this->createSelectBuilder()
            ->from('t1')
            ->suffix('FOR UPDATE')
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 FOR UPDATE',
            [],
            $query
        );
    }

    public function testForUpdate()
    {
        $query = $this->createSelectBuilder()
            ->from('t1')
            ->forUpdate()
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 FOR UPDATE',
            [],
            $query
        );
    }

    public function testUnion()
    {
        $builder1 = $this->createSelectBuilder()->select('c1')->from('t1')->where('c1', '=', 'foo');
        $builder2 = $this->createSelectBuilder()->select('c1')->from('t1')->where('c1', '=', 'bar');

        $query = $builder1->union($builder2)->build();
        $this->assertQueryIs(
            '(SELECT c1 FROM t1 WHERE (c1 = ?)) UNION (SELECT c1 FROM t1 WHERE (c1 = ?))',
            ['foo', 'bar'],
            $query
        );
    }

    public function testUnionAll()
    {
        $builder1 = $this->createSelectBuilder()->select('c1')->from('t1')->where('c1', '=', 'foo');
        $builder2 = $this->createSelectBuilder()->select('c1')->from('t1')->where('c1', '=', 'bar');
        $builder3 = $this->createSelectBuilder()->select('c1')->from('t1')->where('c1', '=', 'baz');

        $query = $builder1->unionAll($builder2)->unionAll($builder3)->build();
        $this->assertQueryIs(
            '(SELECT c1 FROM t1 WHERE (c1 = ?)) UNION ALL (SELECT c1 FROM t1 WHERE (c1 = ?)) UNION ALL (SELECT c1 FROM t1 WHERE (c1 = ?))',
            ['foo', 'bar', 'baz'],
            $query
        );
    }

    public function testAggregate()
    {
        $stmt = $this->createMock(PDOStatementInterface::class);
        $stmt
            ->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $stmt
            ->expects($this->once())
            ->method('fetchColumn')
            ->willReturn(123);

        $pdo = $this->createMock(PDOInterface::class);
        $pdo
            ->expects($this->once())
            ->method('prepare')
            ->with('SELECT COUNT(*)')
            ->willReturn($stmt);

        $builder = $this->createSelectBuilder()->orderBy('c1');
        $this->assertSame(123, $builder->aggregate($pdo, 'COUNT(*)'));
    }

    public function testPaginate()
    {
        $stmt = $this->createMock(PDOStatementInterface::class);
        $stmt
            ->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $stmt
            ->expects($this->once())
            ->method('fetchColumn')
            ->willReturn(1000);

        $pdo = $this->createMock(PDOInterface::class);
        $pdo
            ->expects($this->once())
            ->method('prepare')
            ->willReturn($stmt);

        $fetcher = $this->createMock(FetcherInterface::class);

        $paginator = $this->createSelectBuilder()->paginate($pdo, $fetcher, 100);
        $this->assertInstanceOf(Paginator::class, $paginator);
        $this->assertSame(100, $paginator->getPerPage());
        $this->assertSame(1000, $paginator->getNumItems());
    }
}
