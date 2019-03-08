<?php

namespace Emonkak\Orm\Tests\Pagination;

use Emonkak\Database\PDOInterface;
use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\Grammar\DefaultGrammar;
use Emonkak\Orm\Pagination\PaginatorIterator;
use Emonkak\Orm\Pagination\PrecountPaginator;
use Emonkak\Orm\ResultSet\PaginatedResultSet;
use Emonkak\Orm\ResultSet\ResultSetInterface;
use Emonkak\Orm\Tests\Fixtures\Model;
use Emonkak\Orm\Tests\QueryBuilderTestTrait;

/**
 * @covers Emonkak\Orm\Pagination\PrecountPaginator
 */
class PrecountPaginatorTest extends \PHPUnit_Framework_TestCase
{
    use QueryBuilderTestTrait;

    public function testAt()
    {
        $perPage = 100;
        $numItems = 201;

        $builder = $this->getSelectBuilder()->from('t1');

        $stmt = $this->createMock(PDOStatementInterface::class);
        $stmt
            ->expects($this->exactly(6))
            ->method('bindValue')
            ->withConsecutive(
                [1, $perPage, \PDO::PARAM_INT],
                [2, 0, \PDO::PARAM_INT],
                [1, $perPage, \PDO::PARAM_INT],
                [2, 100, \PDO::PARAM_INT],
                [1, $perPage, \PDO::PARAM_INT],
                [2, 200, \PDO::PARAM_INT]
            )
            ->willReturn(true);

        $pdo = $this->createMock(PDOInterface::class);
        $pdo
            ->expects($this->atLeastOnce())
            ->method('prepare')
            ->with('SELECT * FROM t1 LIMIT ? OFFSET ?')
            ->willReturn($stmt);

        $result = $this->createMock(ResultSetInterface::class);

        $fetcher = $this->createMock(FetcherInterface::class);
        $fetcher
            ->expects($this->atLeastOnce())
            ->method('fetch')
            ->with($this->identicalTo($stmt))
            ->willReturn($result);

        $paginator = new PrecountPaginator($builder, $pdo, $fetcher, $perPage, $numItems);

        $this->assertSame($perPage, $paginator->getPerPage());
        $this->assertSame($numItems, $paginator->getNumItems());
        $this->assertSame(3, $paginator->getNumPages());

        $page = $paginator->at(0);
        $this->assertInstanceOf(PaginatedResultSet::class, $page);
        $this->assertSame(0, $page->getIndex());

        $page = $paginator->at(1);
        $this->assertInstanceOf(PaginatedResultSet::class, $page);
        $this->assertSame(1, $page->getIndex());

        $page = $paginator->at(2);
        $this->assertInstanceOf(PaginatedResultSet::class, $page);
        $this->assertSame(2, $page->getIndex());

        $page = $paginator->at(999);
        $this->assertInstanceOf(PaginatedResultSet::class, $page);
        $this->assertSame(999, $page->getIndex());
    }

    public function testGetClass()
    {
        $perPage = 100;
        $numItems = 0;

        $builder = $this->getSelectBuilder()->from('t1');

        $pdo = $this->createMock(PDOInterface::class);

        $fetcher = $this->createMock(FetcherInterface::class);
        $fetcher
            ->expects($this->once())
            ->method('getClass')
            ->willReturn(Model::class);

        $paginator = new PrecountPaginator($builder, $pdo, $fetcher, $perPage, $numItems);

        $this->assertSame(Model::class, $paginator->getClass());
    }
}
