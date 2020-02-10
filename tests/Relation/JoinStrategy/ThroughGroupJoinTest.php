<?php

declare(strict_types=1);

namespace Emonkak\Orm\Relation\JoinStrategy;

use Emonkak\Orm\Relation\JoinStrategy\ThroughGroupJoin;
use Emonkak\Orm\ResultSet\PreloadedResultSet;
use PHPUnit\Framework\TestCase;

/**
 * @covers Emonkak\Orm\Relation\JoinStrategy\ThroughGroupJoin
 */
class ThroughGroupJoinTest extends TestCase
{
    public function testJoin()
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
            ['program_id' => 2, 'talent_id' => 1],
            ['program_id' => 3, 'talent_id' => 2],
            ['program_id' => 4, 'talent_id' => 2],
            ['program_id' => 5, 'talent_id' => 4],
            ['program_id' => 6, 'talent_id' => 5],
        ];
        $expected = [
            $talents[0] + ['programs' => [$programs[0]['program_id'], $programs[1]['program_id']]],
            $talents[1] + ['programs' => [$programs[2]['program_id'], $programs[3]['program_id']]],
            $talents[2] + ['programs' => []],
            $talents[3] + ['programs' => [$programs[4]['program_id']]],
            $talents[4] + ['programs' => [$programs[5]['program_id']]],
        ];

        $result = (new ThroughGroupJoin('program_id'))
            ->join(
                new PreloadedResultSet($talents, null),
                new PreloadedResultSet($programs, null),
                function($talent) { return $talent['talent_id']; },
                function($program) { return $program['talent_id']; },
                function($talent, $programs) {
                    $talent['programs'] = $programs;
                    return $talent;
                }
            );
        $this->assertEquals($expected, iterator_to_array($result));
    }
}
