<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\Fetcher;

use Emonkak\Database\PDOInterface;
use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\Fetcher\ObjectFetcher;
use Emonkak\Orm\QueryBuilderInterface;
use Emonkak\Orm\ResultSet\ObjectResultSet;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Emonkak\Orm\Fetcher\ObjectFetcher
 */
class ObjectFetcherTest extends TestCase
{
    public function testGetClass(): void
    {
        $pdo = $this->createMock(PDOInterface::class);
        $class = \stdClass::class;
        $constructorArguments = ['foo'];

        $fetcher = new ObjectFetcher($pdo, $class, $constructorArguments);

        $this->assertSame($pdo, $fetcher->getPdo());
        $this->assertSame(\stdClass::class, $fetcher->getClass());
        $this->assertEquals($constructorArguments, $fetcher->getConstructorArguments());
        $this->assertNull((new ObjectFetcher($pdo, $class))->getConstructorArguments());
    }

    public function testFetch(): void
    {
        $class = \stdClass::class;
        $pdo = $this->createMock(PDOInterface::class);
        $constructorArguments = ['foo'];

        $stmt = $this->createMock(PDOStatementInterface::class);

        $queryBuilder = $this->createMock(QueryBuilderInterface::class);
        $queryBuilder
            ->expects($this->once())
            ->method('prepare')
            ->with($this->identicalTo($pdo))
            ->willReturn($stmt);

        $fetcher = new ObjectFetcher($pdo, $class, $constructorArguments);

        $result = $fetcher->fetch($queryBuilder);

        $this->assertInstanceOf(ObjectResultSet::class, $result);
        $this->assertSame($class, $result->getClass());
        $this->assertEquals($constructorArguments, $result->getConstructorArguments());
    }
}
