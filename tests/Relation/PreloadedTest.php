<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\Relation;

use Emonkak\Orm\Relation\Preloaded;
use Emonkak\Orm\Relation\RelationInterface;
use Emonkak\Orm\ResultSet\ResultSetInterface;
use Emonkak\Orm\Tests\Fixtures\Model;
use Emonkak\Orm\Tests\QueryBuilderTestTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers Emonkak\Orm\Relation\Preloaded
 */
class PreloadedTest extends TestCase
{
    use QueryBuilderTestTrait;

    public function testConstructor()
    {
        $relationKey = 'relation_key';
        $outerKey = 'outer_key';
        $innerKey = 'inner_key';
        $innerClass = Model::class;
        $innerElements = [
            new Model([])
        ];

        $relationStrategy = new Preloaded(
            $relationKey,
            $outerKey,
            $innerKey,
            $innerClass,
            $innerElements
        );

        $this->assertSame($relationKey, $relationStrategy->getRelationKey());
        $this->assertSame($outerKey, $relationStrategy->getOuterKey());
        $this->assertSame($innerKey, $relationStrategy->getInnerKey());
        $this->assertSame($innerClass, $relationStrategy->getInnerClass());
        $this->assertSame($innerElements, $relationStrategy->getInnerElements());
    }

    public function testGetResult()
    {
        $outerKeys = [1, 2, 3];
        $items = [
            new Model(['item_id' => 1]),
            new Model(['item_id' => 2]),
            new Model(['item_id' => 3]),
            new Model(['item_id' => 4]),
            new Model(['item_id' => 5])
        ];

        $relationStrategy = new Preloaded(
            'items',
            'item_id',
            'item_id',
            Model::class,
            $items
        );

        $expectedResultSet = [
            $items[0],
            $items[1],
            $items[2]
        ];

        $this->assertSame($expectedResultSet, iterator_to_array($relationStrategy->getResult($outerKeys), false));
    }

    public function testResolveSelectors()
    {
        $relationStrategy = new Preloaded(
            'revision',
            'revision_id',
            'id',
            Model::class,
            []
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
