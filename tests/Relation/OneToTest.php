<?php

namespace Emonkak\Orm\Tests\Relation;

use Emonkak\Database\PDOInterface;
use Emonkak\Database\PDOStatementInterface;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\Relation\OneTo;
use Emonkak\Orm\Relation\RelationInterface;
use Emonkak\Orm\ResultSet\ResultSetInterface;
use Emonkak\Orm\Tests\Fixtures\Model;
use Emonkak\Orm\Tests\QueryBuilderTestTrait;

/**
 * @covers Emonkak\Orm\Relation\OneTo
 */
class OneToTest extends \PHPUnit_Framework_TestCase
{
    use QueryBuilderTestTrait;

    public function testConstructor()
    {
        $relationKey = 'relation_key';
        $table = 'table';
        $outerKey = 'outer_key';
        $innerKey = 'inner_key';

        $pdo = $this->createMock(PDOInterface::class);
        $fetcher = $this->createMock(FetcherInterface::class);
        $builder = $this->getSelectBuilder();

        $relationStrategy = new OneTo(
            $relationKey,
            $table,
            $outerKey,
            $innerKey,
            $pdo,
            $fetcher,
            $builder
        );

        $this->assertSame('relation_key', $relationStrategy->getRelationKey());
        $this->assertSame('table', $relationStrategy->getTable());
        $this->assertSame('outer_key', $relationStrategy->getOuterKey());
        $this->assertSame('inner_key', $relationStrategy->getInnerKey());
        $this->assertSame($pdo, $relationStrategy->getPdo());
        $this->assertSame($fetcher, $relationStrategy->getFetcher());
        $this->assertSame($builder, $relationStrategy->getBuilder());
    }

    public function testGetResult()
    {
        $outerKeys = [1, 2, 3];
        $expectedResultSet = $this->createMock(ResultSetInterface::class);

        $stmt = $this->createMock(PDOStatementInterface::class);
        $stmt
            ->expects($this->exactly(3))
            ->method('bindValue')
            ->withConsecutive(
                [1, 1, \PDO::PARAM_INT],
                [2, 2, \PDO::PARAM_INT],
                [3, 3, \PDO::PARAM_INT]
            )
            ->willReturn(true);

        $pdo = $this->createMock(PDOInterface::class);
        $pdo
            ->expects($this->once())
            ->method('prepare')
            ->with('SELECT * FROM `revisions` WHERE (`revisions`.`revision_id` IN (?, ?, ?))')
            ->willReturn($stmt);

        $fetcher = $this->createMock(FetcherInterface::class);
        $fetcher
            ->expects($this->once())
            ->method('fetch')
            ->with($this->identicalTo($stmt))
            ->willReturn($expectedResultSet);

        $builder = $this->getSelectBuilder();

        $relationStrategy = new OneTo(
            'revisions',
            'revisions',
            'revision_id',
            'revision_id',
            $pdo,
            $fetcher,
            $builder
        );

        $this->assertSame($expectedResultSet, $relationStrategy->getResult($outerKeys));
    }

    public function testWith()
    {
        $relationKey = 'relation_key';
        $table = 'table';
        $outerKey = 'outer_key';
        $innerKey = 'inner_key';

        $pdo = $this->createMock(PDOInterface::class);
        $fetcher = $this->createMock(FetcherInterface::class);
        $builder = $this->getSelectBuilder();

        $childRelation1 = $this->createMock(RelationInterface::class);
        $childRelation2 = $this->createMock(RelationInterface::class);

        $relationStrategy = new OneTo(
            $relationKey,
            $table,
            $outerKey,
            $innerKey,
            $pdo,
            $fetcher,
            $builder
        );

        $newRelationStrategy = $relationStrategy
            ->with($childRelation1)
            ->with($childRelation2);

        $this->assertInstanceOf(OneTo::class, $newRelationStrategy);
        $this->assertNotSame($relationStrategy, $newRelationStrategy);
        $this->assertEquals([$childRelation1, $childRelation2], $newRelationStrategy->getBuilder()->getRelations());
    }

    public function testResolveSelectors()
    {
        $relationStrategy = new OneTo(
            'revision',
            'revisions',
            'revision_id',
            'id',
            $this->createMock(PDOInterface::class),
            $this->createMock(FetcherInterface::class),
            $this->getSelectBuilder()
        );

        $outerKeySelector = $relationStrategy->getOuterKeySelector(Model::class);
        $innerKeySelector = $relationStrategy->getInnerKeySelector(Model::class);
        $resultSelector = $relationStrategy->getResultSelector(Model::class, Model::class);

        $outer = new Model(['revision_id' => 123]);
        $inner = new Model(['id' => 123]);

        $expectedResult = new Model([
            'revision_id' => 123,
            'revision' => new Model(['id' => 123])
        ]);

        $this->assertSame(123, $outerKeySelector($outer));
        $this->assertSame(123, $innerKeySelector($inner));
        $this->assertEquals($expectedResult, $resultSelector($outer, $inner));
    }
}
