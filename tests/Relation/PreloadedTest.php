<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\Relation;

use Emonkak\Orm\Relation\JoinStrategy\JoinStrategyInterface;
use Emonkak\Orm\Relation\Preloaded;
use Emonkak\Orm\Tests\Fixtures\Model;
use Emonkak\Orm\Tests\QueryBuilderTestTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Emonkak\Orm\Relation\Preloaded
 */
class PreloadedTest extends TestCase
{
    use QueryBuilderTestTrait;

    public function testConstructor(): void
    {
        $relationKeyName = 'relation_key';
        $outerKeyName = 'outer_key';
        $innerKeyName = 'inner_key';
        $innerElements = [
            new Model([]),
        ];

        $relationStrategy = new Preloaded(
            $relationKeyName,
            $outerKeyName,
            $innerKeyName,
            $innerElements
        );

        $this->assertSame($relationKeyName, $relationStrategy->getRelationKeyName());
        $this->assertSame($outerKeyName, $relationStrategy->getOuterKeyName());
        $this->assertSame($innerKeyName, $relationStrategy->getInnerKeyName());
        $this->assertSame($innerElements, $relationStrategy->getInnerElements());
    }

    public function testGetResult(): void
    {
        $outerKeys = [1, 2, 3];
        $items = [
            new Model(['item_id' => 1]),
            new Model(['item_id' => 2]),
            new Model(['item_id' => 3]),
            new Model(['item_id' => 4]),
            new Model(['item_id' => 5]),
        ];

        $relationStrategy = new Preloaded(
            'items',
            'item_id',
            'item_id',
            $items
        );

        $expectedResult = [
            $items[0],
            $items[1],
            $items[2],
        ];

        $joinStrategy = $this->createMock(JoinStrategyInterface::class);
        $joinStrategy
            ->expects($this->once())
            ->method('getInnerKeySelector')
            ->willReturn(function(Model $model): int {
                return $model->item_id;
            });

        $this->assertEquals($expectedResult, $relationStrategy->getResult($outerKeys, $joinStrategy));
    }
}
