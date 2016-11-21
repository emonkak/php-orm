<?php

namespace Emonkak\Orm\Tests\Relation;

use Emonkak\Orm\Relation\JoinStrategy\OuterJoin;
use Emonkak\Orm\ResultSet\FrozenResultSet;

/**
 * @covers Emonkak\Orm\Relation\JoinStrategy\OuterJoin
 */
class OuterJoinTest extends \PHPUnit_Framework_TestCase
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
        $users = [
            ['talent_id' => 1, 'user_id' => 139557376],
            ['talent_id' => 2, 'user_id' => 255386927],
            ['talent_id' => 2, 'user_id' => 53669663],
            ['talent_id' => 4, 'user_id' => 2445518118],
            ['talent_id' => 5, 'user_id' => 199932799],
        ];
        $expected = [
            ['talent_id' => 1, 'name' => 'Sumire Uesaka', 'user' => $users[0]],
            ['talent_id' => 2, 'name' => 'Mikako Komatsu', 'user' => $users[1]],
            ['talent_id' => 2, 'name' => 'Mikako Komatsu', 'user' => $users[2]],
            ['talent_id' => 3, 'name' => 'Rumi Okubo', 'user' => null],
            ['talent_id' => 4, 'name' => 'Natsumi Takamori', 'user' => $users[3]],
            ['talent_id' => 5, 'name' => 'Shiori Mikami', 'user' => $users[4]],
        ];

        $result = (new OuterJoin())
            ->join(
                new FrozenResultSet($talents, null),
                new FrozenResultSet($users, null),
                function($talent) { return $talent['talent_id']; },
                function($user) { return $user['talent_id']; },
                function($talent, $user) {
                    $talent['user'] = $user;
                    return $talent;
                }
            );
        $this->assertEquals($expected, iterator_to_array($result));
    }
}
