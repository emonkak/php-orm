<?php

namespace Emonkak\Orm\Tests;

use Emonkak\Database\PDOInterface;
use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\Fetchable;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\Relation\RelationInterface;
use Emonkak\Orm\ResultSet\ResultSetInterface;
use Emonkak\Orm\Sql;
use Emonkak\Orm\Tests\Fixtures\IterableResultSetInterface;

/**
 * @covers Emonkak\Orm\Fetchable
 */
class FetchableTest extends \PHPUnit_Framework_TestCase
{
    public function testGetResult()
    {
        $result = $this->createMock(IterableResultSetInterface::class);
        $result
            ->expects($this->once())
            ->method('getIterator')
            ->willReturn(new \ArrayIterator([
                ['foo' => 1],
                ['foo' => 2],
            ]));

        $relationElements1 = [
             ['bar' => 3],
             ['bar' => 4],
        ];
        $relationElements2 = [
            ['baz' => 5],
            ['baz' => 6],
        ];
        $exceptedElements = [
            ['foo' => 1, 'bar' => 3, 'baz' => 5],
            ['foo' => 2, 'bar' => 4, 'baz' => 6],
        ];

        $pdo = $this->createMock(PDOInterface::class);

        $stmt = $this->createMock(PDOStatementInterface::class);

        $fetchable = $this->getMockForTrait(Fetchable::class);
        $fetchable
            ->expects($this->once())
            ->method('prepare')
            ->with($this->identicalTo($pdo))
            ->willReturn($stmt);

        $fetcher = $this->createMock(FetcherInterface::class);
        $fetcher
            ->expects($this->once())
            ->method('fetch')
            ->with($this->identicalTo($stmt))
            ->willReturn($result);

        $relation1 = $this->createMock(RelationInterface::class);
        $relation1
            ->expects($this->once())
            ->method('associate')
            ->will($this->returnCallback(function($result) use ($relationElements1) {
                foreach ($result as $i => $element) {
                    yield $element + $relationElements1[$i];
                }
            }));

        $relation2 = $this->createMock(RelationInterface::class);
        $relation2
            ->expects($this->once())
            ->method('associate')
            ->will($this->returnCallback(function($result) use ($relationElements2) {
                foreach ($result as $i => $element) {
                    yield $element + $relationElements2[$i];
                }
            }));

        $builder = $fetchable->with($relation1, $relation2);
        $this->assertEquals([$relation1, $relation2], $builder->getRelations());
        $this->assertEquals($exceptedElements, $builder->getResult($pdo, $fetcher)->toArray());
    }
}
