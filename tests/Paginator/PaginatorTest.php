<?php

namespace Emonkak\Orm\Tests\Pagination;

use Emonkak\Database\PDOInterface;
use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\Pagination\Paginator;
use Emonkak\Orm\Pagination\PaginatorIterator;
use Emonkak\Orm\ResultSet\PaginatedResultSet;
use Emonkak\Orm\ResultSet\ResultSetInterface;
use Emonkak\Orm\SelectBuilder;

/**
 * @covers Emonkak\Orm\Pagination\Paginator
 */
class PaginatorTest extends \PHPUnit_Framework_TestCase
{
    public function testGetIterator()
    {
        $builder = (new SelectBuilder())->from('t1');
        $pdo = $this->createMock(PDOInterface::class);
        $fetcher = $this->createMock(FetcherInterface::class);
        $perPage = 100;
        $numItems = 201;

        $paginator = new Paginator($builder, $pdo, $fetcher, $perPage, $numItems);

        $this->assertInstanceOf(PaginatorIterator::class, $paginator->getIterator());
    }

    public function testAt()
    {
        $perPage = 100;
        $numItems = 201;

        $builder = (new SelectBuilder())->from('t1');

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

        $paginator = new Paginator($builder, $pdo, $fetcher, $perPage, $numItems);

        $this->assertSame($perPage, $paginator->getPerPage());
        $this->assertSame($numItems, $paginator->getNumItems());
        $this->assertSame(3, $paginator->getNumPages());

        $page = $paginator->at(0);
        $this->assertInstanceOf(PaginatedResultSet::class, $page);
        $this->assertSame(0, $page->getIndex());
        $this->assertSame(1, $page->getPageNum());

        $page = $paginator->at(1);
        $this->assertInstanceOf(PaginatedResultSet::class, $page);
        $this->assertSame(1, $page->getIndex());
        $this->assertSame(2, $page->getPageNum());

        $page = $paginator->at(2);
        $this->assertInstanceOf(PaginatedResultSet::class, $page);
        $this->assertSame(2, $page->getIndex());
        $this->assertSame(3, $page->getPageNum());

        $page = $paginator->at(999);
        $this->assertInstanceOf(PaginatedResultSet::class, $page);
        $this->assertSame(999, $page->getIndex());
        $this->assertSame(1000, $page->getPageNum());
    }

    /**
     * @expectedException OutOfRangeException
     */
    public function testAtThrowsOutOfRangeException()
    {
        $builder = (new SelectBuilder())->from('t1');
        $perPage = 100;
        $numItems = 201;

        $pdo = $this->createMock(PDOInterface::class);

        $fetcher = $this->createMock(FetcherInterface::class);

        $paginator = new Paginator($builder, $pdo, $fetcher, $perPage, $numItems);
        $paginator->at(-1);
    }

    public function testFirst()
    {
        $builder = (new SelectBuilder())->from('t1');
        $perPage = 100;
        $numItems = 201;

        $stmt = $this->createMock(PDOStatementInterface::class);
        $stmt
            ->expects($this->exactly(2))
            ->method('bindValue')
            ->withConsecutive(
                [1, $perPage, \PDO::PARAM_INT],
                [2, 0, \PDO::PARAM_INT]
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

        $paginator = new Paginator($builder, $pdo, $fetcher, $perPage, $numItems);

        $this->assertSame($perPage, $paginator->getPerPage());
        $this->assertSame($numItems, $paginator->getNumItems());
        $this->assertSame(3, $paginator->getNumPages());

        $page = $paginator->firstPage();
        $this->assertInstanceOf(PaginatedResultSet::class, $page);
        $this->assertSame(0, $page->getIndex());
        $this->assertSame(1, $page->getPageNum());
    }

    public function testLast()
    {
        $perPage = 100;
        $numItems = 201;

        $builder = (new SelectBuilder())->from('t1');

        $stmt = $this->createMock(PDOStatementInterface::class);
        $stmt
            ->expects($this->exactly(2))
            ->method('bindValue')
            ->withConsecutive(
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

        $paginator = new Paginator($builder, $pdo, $fetcher, $perPage, $numItems);

        $this->assertSame($perPage, $paginator->getPerPage());
        $this->assertSame($numItems, $paginator->getNumItems());
        $this->assertSame(3, $paginator->getNumPages());

        $page = $paginator->lastPage();
        $this->assertInstanceOf(PaginatedResultSet::class, $page);
        $this->assertSame(2, $page->getIndex());
        $this->assertSame(3, $page->getPageNum());
    }
}
