<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\Relation;

use Emonkak\Enumerable\EqualityComparer;
use Emonkak\Orm\Relation\JoinStrategy\ThroughOuterJoin;
use PHPUnit\Framework\TestCase;

/**
 * @covers Emonkak\Orm\Relation\JoinStrategy\ThroughOuterJoin
 */
class ThroughOuterJoinTest extends TestCase
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
            $talents[0] + ['program' => $programs[0]['program_id']],
            $talents[1] + ['program' => $programs[1]['program_id']],
            $talents[2] + ['program' => null],
            $talents[3] + ['program' => $programs[2]['program_id']],
            $talents[4] + ['program' => $programs[3]['program_id']],
        ];

        $outerKeySelector = function($talent) { return $talent['talent_id']; };
        $innerKeySelector = function($program) { return $program['talent_id']; };
        $throughKeySelector = function($program) { return $program['program_id']; };
        $resultSelector = function($talent, $program) {
            $talent['program'] = $program;
            return $talent;
        };
        $comparer = EqualityComparer::getInstance();
        $throughOuterJoin = new ThroughOuterJoin(
            $outerKeySelector,
            $innerKeySelector,
            $throughKeySelector,
            $resultSelector,
            $comparer
        );

        $this->assertSame($outerKeySelector, $throughOuterJoin->getOuterKeySelector());
        $this->assertSame($innerKeySelector, $throughOuterJoin->getInnerKeySelector());
        $this->assertSame($throughKeySelector, $throughOuterJoin->getThroughKeySelector());
        $this->assertSame($resultSelector, $throughOuterJoin->getResultSelector());
        $this->assertSame($comparer, $throughOuterJoin->getComparer());

        $result = $throughOuterJoin->join($talents, $programs);
        $result = iterator_to_array($result);
        $this->assertEquals($expectedResult, $result);
    }
}
