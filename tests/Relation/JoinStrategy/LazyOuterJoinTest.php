<?php

namespace Emonkak\Orm\Tests\Relation;

use Emonkak\Orm\Relation\JoinStrategy\LazyOuterJoin;
use Emonkak\Orm\ResultSet\PreloadResultSet;
use Emonkak\Orm\Tests\Stubs\Model;
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
        $users = [
            new Model(['talent_id' => 1, 'user_id' => 139557376]),
            new Model(['talent_id' => 2, 'user_id' => 255386927]),
            new Model(['talent_id' => 4, 'user_id' => 2445518118]),
            new Model(['talent_id' => 5, 'user_id' => 199932799]),
        ];
        $expected = [
            new Model(['talent_id' => 1, 'name' => 'Sumire Uesaka', 'user' => $users[0]]),
            new Model(['talent_id' => 2, 'name' => 'Mikako Komatsu', 'user' => $users[1]]),
            new Model(['talent_id' => 3, 'name' => 'Rumi Okubo', 'user' => new Model([])]),
            new Model(['talent_id' => 4, 'name' => 'Natsumi Takamori', 'user' => $users[2]]),
            new Model(['talent_id' => 5, 'name' => 'Shiori Mikami', 'user' => $users[3]]),
        ];

        $proxyFactory = new LazyLoadingValueHolderFactory();
        $result = (new LazyOuterJoin($proxyFactory))
            ->join(
                new PreloadResultSet($talents, Model::class),
                new PreloadResultSet($users, Model::class),
                function($talent) { return $talent->talent_id; },
                function($user) { return $user->talent_id; },
                function($talent, $user) {
                    $talent->user = $user;
                    return $talent;
                }
            );
        $result = iterator_to_array($result, false);
        $result = array_map(function($talent) {
            $talent->user = new Model($talent->user->toArray());
            return $talent;
        }, $result);

        $this->assertEquals($expected, $result);
    }
}
