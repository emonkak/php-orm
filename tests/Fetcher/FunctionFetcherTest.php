<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\Fetcher;

use Emonkak\Database\PDOInterface;
use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\Fetcher\FunctionFetcher;
use Emonkak\Orm\QueryBuilderInterface;
use Emonkak\Orm\ResultSet\FunctionResultSet;
use Emonkak\Orm\Tests\Fixtures\Model;
use PHPUnit\Framework\TestCase;

/**
 * @covers Emonkak\Orm\Fetcher\FunctionFetcher
 */
class FunctionFetcherTest extends TestCase
{
    public function testConstructor(): void
    {
        $pdo = $this->createMock(PDOInterface::class);
        $class = Model::class;
        $instantiator = function($props) { return new Model($props); };

        $fetcher = new FunctionFetcher($pdo, $class, $instantiator);

        $this->assertSame($pdo, $fetcher->getPdo());
        $this->assertSame($class, $fetcher->getClass());
        $this->assertSame($instantiator, $fetcher->getInstantiator());
    }

    public function testOfConstructor(): void
    {
        $pdo = $this->createMock(PDOInterface::class);
        $class = Model::class;

        $fetcher = FunctionFetcher::ofConstructor($pdo, $class);
        $instantiator = $fetcher->getInstantiator();

        $this->assertEquals(new Model(['foo' => 'bar']), $instantiator(['foo' => 'bar']));
    }

    public function testFetch(): void
    {
        $pdo = $this->createMock(PDOInterface::class);
        $class = Model::class;
        $instantiator = function($props) { return new Model($props); };

        $stmt = $this->createMock(PDOStatementInterface::class);

        $queryBuilder = $this->createMock(QueryBuilderInterface::class);
        $queryBuilder
            ->expects($this->once())
            ->method('prepare')
            ->with($this->identicalTo($pdo))
            ->willReturn($stmt);

        $fetcher = new FunctionFetcher($pdo, $class, $instantiator);

        $result = $fetcher->fetch($queryBuilder);

        $this->assertInstanceOf(FunctionResultSet::class, $result);
        $this->assertEquals($class, $result->getClass());
        $this->assertEquals($instantiator, $result->getInstantiator());
    }
}
