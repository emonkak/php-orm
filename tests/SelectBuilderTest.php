<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests;

use Emonkak\Database\PDOInterface;
use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\Grammar\GrammarInterface;
use Emonkak\Orm\Pagination\PrecountPaginator;
use Emonkak\Orm\ResultSet\PreloadedResultSet;
use Emonkak\Orm\SelectBuilder;
use Emonkak\Orm\Sql;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Emonkak\Orm\SelectBuilder
 */
class SelectBuilderTest extends TestCase
{
    use QueryBuilderTestTrait;

    public function testGetGrammar(): void
    {
        $grammar = $this->createMock(GrammarInterface::class);
        $queryBuilder = new SelectBuilder($grammar);
        $this->assertSame($grammar, $queryBuilder->getGrammar());
    }

    public function testGetAccesors(): void
    {
        $unionBuilder = $this->getSelectBuilder()->select('c1')->from('t1');

        $queryBuilder = $this->getSelectBuilder()
            ->select('c1')
            ->from('t1')
            ->join('t2', 't1.id = t2.id')
            ->where('t1.c1', '=', 123)
            ->groupBy('t1.c1')
            ->orderBy('t1.c2')
            ->window('w', 'PARTITION BY c1')
            ->having('t1.c2', '=', 456)
            ->offset(12)
            ->limit(34)
            ->suffix('FOR UPDATE')
            ->unionWith($unionBuilder);

        $this->assertSame('SELECT', $queryBuilder->getPrefix());
        $this->assertEquals([new Sql('c1')], $queryBuilder->getSelectBuilder());
        $this->assertEquals([new Sql('t1')], $queryBuilder->getFrom());
        $this->assertEquals([new Sql('JOIN t2 ON t1.id = t2.id', [])], $queryBuilder->getJoin());
        $this->assertQueryIs('(t1.c1 = ?)', [123], $queryBuilder->getWhere());
        $this->assertEquals([new Sql('t1.c1')], $queryBuilder->getGroupBy());
        $this->assertEquals([new Sql('t1.c2')], $queryBuilder->getOrderBy());
        $this->assertQueryIs('(t1.c2 = ?)', [456], $queryBuilder->getHaving()->build());
        $this->assertEquals([new Sql('w AS (PARTITION BY c1)', [])], $queryBuilder->getWindow());
        $this->assertSame(12, $queryBuilder->getOffset());
        $this->assertSame(34, $queryBuilder->getLimit());
        $this->assertSame('FOR UPDATE', $queryBuilder->getSuffix());
        $this->assertEquals([new Sql('UNION (SELECT c1 FROM t1)')], $queryBuilder->getUnion());
    }

    public function testSelect(): void
    {
        $query = $this->getSelectBuilder()
            ->from('t1')
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1',
            [],
            $query
        );

        $query = $this->getSelectBuilder()
            ->select('1')
            ->build();
        $this->assertQueryIs(
            'SELECT 1',
            [],
            $query
        );

        $query = $this->getSelectBuilder()
            ->select('c1')
            ->select('c2')
            ->from('t1')
            ->build();
        $this->assertQueryIs(
            'SELECT c1, c2 FROM t1',
            [],
            $query
        );

        $query = $this->getSelectBuilder()
            ->select('c1', 'a1')
            ->select('c2', 'a2')
            ->from('t1')
            ->build();
        $this->assertQueryIs(
            'SELECT c1 AS a1, c2 AS a2 FROM t1',
            [],
            $query
        );

        $query = $this->getSelectBuilder()
            ->select(new Sql('? + 1', [100]), 'c1')
            ->from('t1')
            ->build();
        $this->assertQueryIs(
            'SELECT ? + 1 AS c1 FROM t1',
            [100],
            $query
        );

        $queryBuilder = $this->getSelectBuilder()->from('t2')->where('c1', '=', 'foo');
        $query = $this->getSelectBuilder()
            ->select($queryBuilder, 'c1')
            ->from('t1')
            ->build();
        $this->assertQueryIs(
            'SELECT (SELECT * FROM t2 WHERE (c1 = ?)) AS c1 FROM t1',
            ['foo'],
            $query
        );
    }

    public function testSelectAll(): void
    {
        $query = $this->getSelectBuilder()
            ->withSelect([
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

    public function testPrefix(): void
    {
        $query = $this->getSelectBuilder()
            ->prefix('SELECT SQL_CALC_FOUND_ROWS')
            ->from('t1')
            ->build();
        $this->assertQueryIs(
            'SELECT SQL_CALC_FOUND_ROWS * FROM t1',
            [],
            $query
        );
    }

    public function testFrom(): void
    {
        $query = $this->getSelectBuilder()
            ->select('c1')
            ->from('t1', 'a1')
            ->build();
        $this->assertQueryIs(
            'SELECT c1 FROM t1 AS a1',
            [],
            $query
        );

        $query = $this->getSelectBuilder()
            ->from('t1')
            ->from('t2')
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1, t2',
            [],
            $query
        );

        $query = $this->getSelectBuilder()
            ->from('t2')
            ->from('t1', null, 0)
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1, t2',
            [],
            $query
        );

        $queryBuilder = $this->getSelectBuilder()->from('t1');
        $query = $this->getSelectBuilder()
            ->from($queryBuilder, 'a1')
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM (SELECT * FROM t1) AS a1',
            [],
            $query
        );
    }

    public function testJoin(): void
    {
        $query = $this->getSelectBuilder()
            ->from('t1')
            ->join('t2', 't1.id = t2.id')
            ->join('t3', 't2.id = t3.id')
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 JOIN t2 ON t1.id = t2.id JOIN t3 ON t2.id = t3.id',
            [],
            $query
        );

        $query = $this->getSelectBuilder()
            ->from('t1')
            ->join('t3', 't2.id = t3.id')
            ->join('t2', 't1.id = t2.id', null, 0)
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 JOIN t2 ON t1.id = t2.id JOIN t3 ON t2.id = t3.id',
            [],
            $query
        );

        $query = $this->getSelectBuilder()
            ->from('t1')
            ->outerJoin('t2', 't1.id = t2.id')
            ->join('t3', 't2.id = t3.id', null, -1, 'INNER JOIN')
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 LEFT OUTER JOIN t2 ON t1.id = t2.id INNER JOIN t3 ON t2.id = t3.id',
            [],
            $query
        );

        $queryBuilder = $this->getSelectBuilder()->from('t2');
        $query = $this->getSelectBuilder()
            ->from('t1')
            ->join($queryBuilder, 't1.id = t2.id', 't2')
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 JOIN (SELECT * FROM t2) AS t2 ON t1.id = t2.id',
            [],
            $query
        );
    }

    public function testWhereEqual(): void
    {
        $query = $this->getSelectBuilder()
            ->from('t1')
            ->where('c1', '=', 'foo')
            ->where('c2', 'IS', null)
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 WHERE ((c1 = ?) AND (c2 IS NULL))',
            ['foo'],
            $query
        );

        $query = $this->getSelectBuilder()
            ->from('t1')
            ->where('c1', '=', 'var_dump')
            ->where('c2', '=', 'var_dump')
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 WHERE ((c1 = ?) AND (c2 = ?))',
            ['var_dump', 'var_dump'],
            $query
        );

        $query = $this->getSelectBuilder()
            ->from('t1')
            ->where('c1', '=', 'foo')
            ->where('c2', 'IS', null)
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 WHERE ((c1 = ?) AND (c2 IS NULL))',
            ['foo'],
            $query
        );

        $query = $this->getSelectBuilder()
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

        $queryBuilder = $this->getSelectBuilder()->select('c1')->from('t2')->where('c2', '=', 'foo')->limit(1);
        $query = $this->getSelectBuilder()
            ->from('t1')
            ->where($queryBuilder, '=', 'bar')
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 WHERE ((SELECT c1 FROM t2 WHERE (c2 = ?) LIMIT ?) = ?)',
            ['foo', 1, 'bar'],
            $query
        );

        $query = $this->getSelectBuilder()
            ->from('t1')
            ->where('c1', '=', $queryBuilder)
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 WHERE (c1 = (SELECT c1 FROM t2 WHERE (c2 = ?) LIMIT ?))',
            ['foo', 1],
            $query
        );
    }

    public function testWhereComparing(): void
    {
        $query = $this->getSelectBuilder()
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

    public function testWhereLike(): void
    {
        $query = $this->getSelectBuilder()
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

    public function testWhereBetween(): void
    {
        $query = $this->getSelectBuilder()
            ->from('t1')
            ->where('c1', 'BETWEEN', 1, 10)
            ->where('c2', 'NOT BETWEEN', 2, 20)
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 WHERE ((c1 BETWEEN ? AND ?) AND (c2 NOT BETWEEN ? AND ?))',
            [1, 10, 2, 20],
            $query
        );

        $queryBuilder = $this->getSelectBuilder()->select('c1')->from('t2')->where('c2', '=', 'foo');
        $query = $this->getSelectBuilder()
            ->from('t1')
            ->where($queryBuilder, 'BETWEEN', 1, 10)
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 WHERE ((SELECT c1 FROM t2 WHERE (c2 = ?)) BETWEEN ? AND ?)',
            ['foo', 1, 10],
            $query
        );
    }

    public function testWhereIn(): void
    {
        $query = $this->getSelectBuilder()
            ->from('t1')
            ->where('c1', 'IN', [1, 2, 3])
            ->where('c2', 'NOT IN', [10, 20, 30])
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 WHERE ((c1 IN (?, ?, ?)) AND (c2 NOT IN (?, ?, ?)))',
            [1, 2, 3, 10, 20, 30],
            $query
        );

        $queryBuilder = $this->getSelectBuilder()->select('c1')->from('t2')->where('c2', '=', 'foo');
        $query = $this->getSelectBuilder()
            ->from('t1')
            ->where('c1', 'IN', $queryBuilder)
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 WHERE (c1 IN (SELECT c1 FROM t2 WHERE (c2 = ?)))',
            ['foo'],
            $query
        );

        $queryBuilder = $this->getSelectBuilder()->select('c1')->from('t2')->where('c2', '=', 'foo')->limit(1);
        $query = $this->getSelectBuilder()
            ->from('t1')
            ->where($queryBuilder, 'IN', [1, 2, 3])
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 WHERE ((SELECT c1 FROM t2 WHERE (c2 = ?) LIMIT ?) IN (?, ?, ?))',
            ['foo', 1, 1, 2, 3],
            $query
        );
    }

    public function testWhereSql(): void
    {
        $query = $this->getSelectBuilder()
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

    public function testWhereExists(): void
    {
        $queryBuilder = $this->getSelectBuilder()->select('c1')->from('t2')->where('c2', '=', 'foo')->limit(1);
        $query = $this->getSelectBuilder()
            ->from('t1')
            ->where('EXISTS', $queryBuilder)
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 WHERE (EXISTS (SELECT c1 FROM t2 WHERE (c2 = ?) LIMIT ?))',
            ['foo', 1],
            $query
        );

        $query = $this->getSelectBuilder()
            ->from('t1')
            ->where('NOT EXISTS', $queryBuilder)
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 WHERE (NOT EXISTS (SELECT c1 FROM t2 WHERE (c2 = ?) LIMIT ?))',
            ['foo', 1],
            $query
        );
    }

    public function testWhereIsNull(): void
    {
        $queryBuilder = $this->getSelectBuilder()->select('c1')->from('t2')->where('c2', '=', 'foo')->limit(1);
        $query = $this->getSelectBuilder()
            ->from('t1')
            ->where($queryBuilder, 'IS', null)
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 WHERE ((SELECT c1 FROM t2 WHERE (c2 = ?) LIMIT ?) IS NULL)',
            ['foo', 1],
            $query
        );

        $queryBuilder = $this->getSelectBuilder()->select('c1')->from('t2')->where('c2', '=', 'foo')->limit(1);
        $query = $this->getSelectBuilder()
            ->from('t1')
            ->where($queryBuilder, 'IS NOT', null)
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 WHERE ((SELECT c1 FROM t2 WHERE (c2 = ?) LIMIT ?) IS NOT NULL)',
            ['foo', 1],
            $query
        );
    }

    public function testOrWhere(): void
    {
        $query = $this->getSelectBuilder()
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

    public function testGroupBy(): void
    {
        $query = $this->getSelectBuilder()
            ->from('t1')
            ->groupBy('c1')
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 GROUP BY c1',
            [],
            $query
        );

        $query = $this->getSelectBuilder()
            ->from('t1')
            ->groupBy('c1')
            ->groupBy('c2')
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 GROUP BY c1, c2',
            [],
            $query
        );
    }

    public function testHaving(): void
    {
        $query = $this->getSelectBuilder()
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

        $query = $this->getSelectBuilder()
            ->from('t1')
            ->groupBy('c1')
            ->having('c2', '!=', 'foo')
            ->having('c3', '<>', 'bar')
            ->having('c4', 'IS NOT', null)
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 GROUP BY c1 HAVING (((c2 != ?) AND (c3 <> ?)) AND (c4 IS NOT NULL))',
            ['foo', 'bar'],
            $query
        );
    }

    public function testOrHaving(): void
    {
        $query = $this->getSelectBuilder()
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

    public function testWindow(): void
    {
        $query = $this->getSelectBuilder()
            ->select('ROW_NUMBER() OVER w')
            ->from('t1')
            ->window('w1', 'PARTITION BY c1')
            ->window('w2')
            ->build();
        $this->assertQueryIs(
            'SELECT ROW_NUMBER() OVER w FROM t1 WINDOW w1 AS (PARTITION BY c1), w2 AS ()',
            [],
            $query
        );
    }

    public function testOrderBy(): void
    {
        $query = $this->getSelectBuilder()
            ->from('t1')
            ->orderBy('c1')
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 ORDER BY c1',
            [],
            $query
        );

        $query = $this->getSelectBuilder()
            ->from('t1')
            ->orderBy('NULL')
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 ORDER BY NULL',
            [],
            $query
        );

        $query = $this->getSelectBuilder()
            ->from('t1')
            ->orderBy(new Sql('c1 + ?', [1]), 'DESC')
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 ORDER BY c1 + ? DESC',
            [1],
            $query
        );

        $queryBuilder = $this->getSelectBuilder()->select('c1')->from('t2')->limit(1);
        $query = $this->getSelectBuilder()
            ->from('t1')
            ->orderBy($queryBuilder, 'DESC')
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 ORDER BY (SELECT c1 FROM t2 LIMIT ?) DESC',
            [1],
            $query
        );
    }

    public function testLimit(): void
    {
        $query = $this->getSelectBuilder()
            ->from('t1')
            ->limit(10)
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 LIMIT ?',
            [10],
            $query
        );
    }

    public function testOffset(): void
    {
        $query = $this->getSelectBuilder()
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

    public function testSuffix(): void
    {
        $query = $this->getSelectBuilder()
            ->from('t1')
            ->suffix('FOR UPDATE')
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 FOR UPDATE',
            [],
            $query
        );
    }

    public function testForUpdate(): void
    {
        $query = $this->getSelectBuilder()
            ->from('t1')
            ->forUpdate()
            ->build();
        $this->assertQueryIs(
            'SELECT * FROM t1 FOR UPDATE',
            [],
            $query
        );
    }

    public function testUnion(): void
    {
        $query = $this->getSelectBuilder()
            ->select('c1')
            ->from('t1')
            ->where('c1', '=', 'foo')
            ->union()
            ->select('c2')
            ->from('t2')
            ->where('c2', '=', 'bar')
            ->build();

        $this->assertQueryIs(
            'SELECT c1 FROM t1 WHERE (c1 = ?) UNION SELECT c2 FROM t2 WHERE (c2 = ?)',
            ['foo', 'bar'],
            $query
        );
    }

    public function testUnionWith(): void
    {
        $queryBuilder1 = $this->getSelectBuilder()->select('c1')->from('t1')->where('c1', '=', 'foo');
        $queryBuilder2 = $this->getSelectBuilder()->select('c2')->from('t2')->where('c2', '=', 'bar');

        $query = $queryBuilder1->unionWith($queryBuilder2)->build();
        $this->assertQueryIs(
            'SELECT c1 FROM t1 WHERE (c1 = ?) UNION (SELECT c2 FROM t2 WHERE (c2 = ?))',
            ['foo', 'bar'],
            $query
        );
    }

    public function testUnionAll(): void
    {
        $query = $this->getSelectBuilder()
            ->select('c1')
            ->from('t1')
            ->where('c1', '=', 'foo')
            ->unionAll()
            ->select('c2')
            ->from('t2')
            ->where('c2', '=', 'bar')
            ->unionAll()
            ->select('c3')
            ->from('t3')
            ->where('c3', '=', 'baz')
            ->build();

        $this->assertQueryIs(
            'SELECT c1 FROM t1 WHERE (c1 = ?) UNION ALL SELECT c2 FROM t2 WHERE (c2 = ?) UNION ALL SELECT c3 FROM t3 WHERE (c3 = ?)',
            ['foo', 'bar', 'baz'],
            $query
        );
    }

    public function testUnionAllWith(): void
    {
        $queryBuilder1 = $this->getSelectBuilder()->select('c1')->from('t1')->where('c1', '=', 'foo');
        $queryBuilder2 = $this->getSelectBuilder()->select('c2')->from('t2')->where('c2', '=', 'bar');
        $queryBuilder3 = $this->getSelectBuilder()->select('c3')->from('t3')->where('c3', '=', 'baz');

        $query = $queryBuilder1->unionAllWith($queryBuilder2)->unionAllWith($queryBuilder3)->build();
        $this->assertQueryIs(
            'SELECT c1 FROM t1 WHERE (c1 = ?) UNION ALL (SELECT c2 FROM t2 WHERE (c2 = ?)) UNION ALL (SELECT c3 FROM t3 WHERE (c3 = ?))',
            ['foo', 'bar', 'baz'],
            $query
        );
    }

    public function testAggregate(): void
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

        $queryBuilder = $this->getSelectBuilder()->orderBy('c1');
        $this->assertSame(123, $queryBuilder->aggregate($pdo, 'COUNT(*)'));
    }

    public function testPaginate(): void
    {
        $perPage = 10;
        $totalItems = 21;

        $expectedResult = array_fill(0, 10, new \stdClass());

        $stmt1 = $this->createMock(PDOStatementInterface::class);
        $stmt1
            ->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $stmt1
            ->expects($this->once())
            ->method('fetchColumn')
            ->willReturn($totalItems);

        $stmt2 = $this->createMock(PDOStatementInterface::class);
        $stmt2
            ->expects($this->exactly(2))
            ->method('bindValue')
            ->withConsecutive(
                [1, $perPage, \PDO::PARAM_INT],
                [2, 0, \PDO::PARAM_INT]
            )
            ->willReturn(true);

        $pdo = $this->createMock(PDOInterface::class);
        $pdo
            ->expects($this->exactly(2))
            ->method('prepare')
            ->withConsecutive(
                ['SELECT COUNT(*) FROM t1'],
                ['SELECT * FROM t1 ORDER BY t1.id LIMIT ? OFFSET ?']
            )
            ->will($this->onConsecutiveCalls(
                $stmt1,
                $stmt2
            ));

        $fetcher = $this->createMock(FetcherInterface::class);
        $fetcher
            ->expects($this->once())
            ->method('getPdo')
            ->willReturn($pdo);
        $fetcher
            ->expects($this->once())
            ->method('fetch')
            ->will($this->returnCallback(function($queryBuilder) use ($pdo, $expectedResult) {
                $queryBuilder->prepare($pdo);
                return new PreloadedResultSet($expectedResult);
            }));

        $paginator = $this->getSelectBuilder()
            ->from('t1')
            ->orderBy('t1.id')
            ->paginate($fetcher, $perPage);

        $this->assertInstanceOf(PrecountPaginator::class, $paginator);

        $page = $paginator->at(0);
        $this->assertSame($perPage, $paginator->getPerPage());
        $this->assertSame($totalItems, $paginator->getTotalItems());
        $this->assertSame($expectedResult, iterator_to_array($page));
    }

    public function testPaginateFrom(): void
    {
        $index = 1;
        $perPage = 10;

        $result = array_fill(0, 11, new \stdClass());
        $expectedResult = array_slice($result, 0, $perPage);

        $stmt = $this->createMock(PDOStatementInterface::class);
        $stmt
            ->expects($this->exactly(2))
            ->method('bindValue')
            ->withConsecutive(
                [1, 11, \PDO::PARAM_INT],
                [2, 10, \PDO::PARAM_INT]
            )
            ->willReturn(true);

        $pdo = $this->createMock(PDOInterface::class);
        $pdo
            ->expects($this->once())
            ->method('prepare')
            ->with('SELECT * FROM t1 ORDER BY t1.id LIMIT ? OFFSET ?')
            ->willReturn($stmt);

        $fetcher = $this->createMock(FetcherInterface::class);
        $fetcher
            ->expects($this->once())
            ->method('fetch')
            ->will($this->returnCallback(function($queryBuilder) use ($pdo, $result) {
                $queryBuilder->prepare($pdo);
                return new PreloadedResultSet($result);
            }));

        $sequentialPage = $this->getSelectBuilder()
            ->from('t1')
            ->orderBy('t1.id')
            ->paginateFrom($fetcher, $index, $perPage);

        $this->assertEquals($expectedResult, iterator_to_array($sequentialPage));
        $this->assertTrue($sequentialPage->hasNext());
    }
}
