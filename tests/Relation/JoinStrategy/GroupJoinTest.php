<?php

namespace Emonkak\Orm\Tests\Relation;

use Emonkak\Orm\Relation\JoinStrategy\GroupJoin;
use Emonkak\Orm\ResultSet\PreloadResultSet;

/**
 * @covers Emonkak\Orm\Relation\JoinStrategy\GroupJoin
 */
class GroupJoinTest extends \PHPUnit_Framework_TestCase
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
            $talents[0] + ['programs' => [$programs[0], $programs[1]]],
            $talents[1] + ['programs' => [$programs[2], $programs[3]]],
            $talents[2] + ['programs' => []],
            $talents[3] + ['programs' => [$programs[4]]],
            $talents[4] + ['programs' => [$programs[5]]],
        ];

        $result = (new GroupJoin())
            ->join(
                new PreloadResultSet($talents, null),
                new PreloadResultSet($programs, null),
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
