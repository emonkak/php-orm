<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\Relation;

use Emonkak\Enumerable\EqualityComparer;
use Emonkak\Orm\Relation\JoinStrategy\OuterJoin;
use PHPUnit\Framework\TestCase;

/**
 * @covers Emonkak\Orm\Relation\JoinStrategy\OuterJoin
 */
class OuterJoinTest extends TestCase
{
    public function testJoin(): void
    {
        $talents = [
            ['talent_id' => 1, 'name' => 'Sumire Uesaka'],
            ['talent_id' => 2, 'name' => 'Mikako Komatsu'],
            ['talent_id' => 3, 'name' => 'Rumi Okubo'],
            ['talent_id' => 4, 'name' => 'Natsumi Takamori'],
            ['talent_id' => 5, 'name' => 'Shiori Mikami'],
        ];
        $programs = [
            ['program_id' => 1, 'talent_id' => 1],
            ['program_id' => 3, 'talent_id' => 2],
            ['program_id' => 5, 'talent_id' => 4],
            ['program_id' => 6, 'talent_id' => 5],
        ];
        $expectedResult = [
            $talents[0] + ['program' => $programs[0]],
            $talents[1] + ['program' => $programs[1]],
            $talents[2] + ['program' => null],
            $talents[3] + ['program' => $programs[2]],
            $talents[4] + ['program' => $programs[3]],
        ];

        $outerKeySelector = function($talent) { return $talent['talent_id']; };
        $innerKeySelector = function($program) { return $program['talent_id']; };
        $resultSelector = function($talent, $program) {
            $talent['program'] = $program;
            return $talent;
        };
        $comparer = EqualityComparer::getInstance();
        $outerJoin = new OuterJoin(
            $outerKeySelector,
            $innerKeySelector,
            $resultSelector,
            $comparer
        );

        $this->assertSame($outerKeySelector, $outerJoin->getOuterKeySelector());
        $this->assertSame($innerKeySelector, $outerJoin->getInnerKeySelector());
        $this->assertSame($resultSelector, $outerJoin->getResultSelector());
        $this->assertSame($comparer, $outerJoin->getComparer());

        $result = $outerJoin->join($talents, $programs);
        $result = iterator_to_array($result);
        $this->assertEquals($expectedResult, $result);
    }
}
