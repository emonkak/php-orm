<?php

namespace Emonkak\Orm\Relation\JoinStrategy;

use Emonkak\Orm\Relation\JoinStrategy\ThroughGroupJoin;
use Emonkak\Orm\ResultSet\PreloadResultSet;

/**
 * @covers Emonkak\Orm\Relation\JoinStrategy\ThroughGroupJoin
 */
class ThroughGroupJoinTest extends \PHPUnit_Framework_TestCase
{
    public function testJoin()
    {
        $users = [
            (object) ['user_id' => 1, 'name' => 'Sumire Uesaka'],
            (object) ['user_id' => 2, 'name' => 'Mikako Komatsu'],
            (object) ['user_id' => 3, 'name' => 'Rumi Okubo'],
            (object) ['user_id' => 4, 'name' => 'Natsumi Takamori'],
            (object) ['user_id' => 5, 'name' => 'Shiori Mikami'],
        ];
        $tweets = [
            (object) ['user_id' => 1, 'body' => 'foo'],
            (object) ['user_id' => 1, 'body' => 'bar'],
            (object) ['user_id' => 1, 'body' => 'baz'],
            (object) ['user_id' => 3, 'body' => 'hoge'],
            (object) ['user_id' => 3, 'body' => 'fuga'],
            (object) ['user_id' => 5, 'body' => 'piyo']
        ];
        $expected = [
            (object) [
                'user_id' => 1,
                'name' => 'Sumire Uesaka',
                'tweets' => ['foo', 'bar', 'baz'],
            ],
            (object) [
                'user_id' => 2,
                'name' => 'Mikako Komatsu',
                'tweets' => [],
            ],
            (object) [
                'user_id' => 3,
                'name' => 'Rumi Okubo',
                'tweets' => ['hoge', 'fuga'],
            ],
            (object) [
                'user_id' => 4,
                'name' => 'Natsumi Takamori',
                'tweets' => [],
            ],
            (object) [
                'user_id' => 5,
                'name' => 'Shiori Mikami',
                'tweets' => ['piyo'],
            ],
        ];

        $result = (new ThroughGroupJoin('body'))
            ->join(
                new PreloadResultSet($users, null),
                new PreloadResultSet($tweets, null),
                function($user) { return $user->user_id; },
                function($user) { return $user->user_id; },
                function($user, $tweets) {
                    $user->tweets = $tweets;
                    return $user;
                }
            );
        $this->assertEquals($expected, iterator_to_array($result));
    }
}
