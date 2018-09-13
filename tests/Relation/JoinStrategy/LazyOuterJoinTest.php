<?php

namespace Emonkak\Orm\Tests\Relation;

use Emonkak\Orm\Relation\JoinStrategy\LazyOuterJoin;
use Emonkak\Orm\ResultSet\PreloadedResultSet;
use Emonkak\Orm\Tests\Fixtures\Model;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;

/**
 * @covers Emonkak\Orm\Relation\JoinStrategy\LazyOuterJoin
 */
class LazyOuterJoinTest extends \PHPUnit_Framework_TestCase
{
    public function testJoin()
    {
        $talents = [
            new Model(['talent_id' => 1, 'name' => 'Sumire Uesaka']),
            new Model(['talent_id' => 2, 'name' => 'Mikako Komatsu']),
            new Model(['talent_id' => 3, 'name' => 'Rumi Okubo']),
            new Model(['talent_id' => 4, 'name' => 'Natsumi Takamori']),
            new Model(['talent_id' => 5, 'name' => 'Shiori Mikami']),
        ];
        $programs = [
            new Model(['program_id' => 1, 'talent_id' => 1]),
            new Model(['program_id' => 3, 'talent_id' => 2]),
            new Model(['program_id' => 5, 'talent_id' => 4]),
            new Model(['program_id' => 6, 'talent_id' => 5]),
        ];
        $expected = [
            new Model($talents[0]->toArray() + ['program' => $programs[0]]),
            new Model($talents[1]->toArray() + ['program' => $programs[1]]),
            new Model($talents[2]->toArray() + ['program' => new Model([])]),
            new Model($talents[3]->toArray() + ['program' => $programs[2]]),
            new Model($talents[4]->toArray() + ['program' => $programs[3]]),
        ];

        $proxyFactory = new LazyLoadingValueHolderFactory();
        $result = (new LazyOuterJoin($proxyFactory))
            ->join(
                new PreloadedResultSet($talents, Model::class),
                new PreloadedResultSet($programs, Model::class),
                function($talent) { return $talent->talent_id; },
                function($program) { return $program->talent_id; },
                function($talent, $program) {
                    $talent->program = new Model($program->toArray());
                    return $talent;
                }
            );
        $this->assertEquals($expected, iterator_to_array($result));
    }
}
