<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\Relation;

use Emonkak\Enumerable\LooseEqualityComparer;
use Emonkak\Orm\Relation\JoinStrategy\LazyOuterJoin;
use Emonkak\Orm\Relation\JoinStrategy\LazyValue;
use Emonkak\Orm\Tests\Fixtures\Model;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Emonkak\Orm\Relation\JoinStrategy\LazyOuterJoin
 */
class LazyOuterJoinTest extends TestCase
{
    public function testJoin(): void
    {
        $talents = [
            new Model(['talent_id' => 1, 'name' => 'Sumire Uesaka']),
            new Model(['talent_id' => 2, 'name' => 'Mikako Komatsu']),
            new Model(['talent_id' => 3, 'name' => 'Rumi Okubo']),
            new Model(['talent_id' => 4, 'name' => 'Natsumi Takamori']),
            new Model(['talent_id' => 5, 'name' => 'Shiori Mikami']),
        ];
        $programs = [
            new Model(['program_id' => 1, 'talent_id' => '1']),
            new Model(['program_id' => 3, 'talent_id' => '2']),
            new Model(['program_id' => 5, 'talent_id' => '4']),
            new Model(['program_id' => 6, 'talent_id' => '5']),
        ];
        $expectedResult = [
            new Model($talents[0]->toArray() + ['program' => $programs[0]]),
            new Model($talents[1]->toArray() + ['program' => $programs[1]]),
            new Model($talents[2]->toArray() + ['program' => null]),
            new Model($talents[3]->toArray() + ['program' => $programs[2]]),
            new Model($talents[4]->toArray() + ['program' => $programs[3]]),
        ];

        // Test whether IDs of different types can be joined.
        $outerKeySelector = function(Model $talent): int { return $talent->talent_id; };
        $innerKeySelector = function(Model $program): string { return $program->talent_id; };
        $resultSelector = function(Model $talent, LazyValue $program): Model {
            $talent->program = $program;
            return $talent;
        };
        /** @var LooseEqualityComparer<mixed> */
        $comparer = LooseEqualityComparer::getInstance();

        $lazyOuterJoin = new LazyOuterJoin(
            $outerKeySelector,
            $innerKeySelector,
            $resultSelector,
            $comparer
        );

        $this->assertSame($outerKeySelector, $lazyOuterJoin->getOuterKeySelector());
        $this->assertSame($innerKeySelector, $lazyOuterJoin->getInnerKeySelector());
        $this->assertSame($resultSelector, $lazyOuterJoin->getResultSelector());
        $this->assertSame($comparer, $lazyOuterJoin->getComparer());

        $result = $lazyOuterJoin
            ->join($talents, $programs);
        $result = iterator_to_array($result, false);
        $result = array_map(function(Model $talent): Model {
            $talent->program = $talent->program->get();
            return $talent;
        }, $result);
        $this->assertEquals($expectedResult, $result);
    }
}
