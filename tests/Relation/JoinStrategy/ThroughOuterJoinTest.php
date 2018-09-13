<?php

namespace Emonkak\Orm\Tests\Relation;

use Emonkak\Orm\Relation\JoinStrategy\ThroughOuterJoin;
use Emonkak\Orm\ResultSet\PreloadedResultSet;

/**
 * @covers Emonkak\Orm\Relation\JoinStrategy\ThroughOuterJoin
 */
class ThroughOuterJoinTest extends \PHPUnit_Framework_TestCase
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
            ['program_id' => 3, 'talent_id' => 2],
            ['program_id' => 5, 'talent_id' => 4],
            ['program_id' => 6, 'talent_id' => 5],
        ];
        $expected = [
            $talents[0] + ['program' => $programs[0]['program_id']],
            $talents[1] + ['program' => $programs[1]['program_id']],
            $talents[2] + ['program' => null],
            $talents[3] + ['program' => $programs[2]['program_id']],
            $talents[4] + ['program' => $programs[3]['program_id']],
        ];

        $result = (new ThroughOuterJoin('program_id'))
            ->join(
                new PreloadedResultSet($talents, null),
                new PreloadedResultSet($programs, null),
                function($talent) { return $talent['talent_id']; },
                function($program) { return $program['talent_id']; },
                function($talent, $program) {
                    $talent['program'] = $program;
                    return $talent;
                }
            );
        $this->assertEquals($expected, iterator_to_array($result));
    }
}
