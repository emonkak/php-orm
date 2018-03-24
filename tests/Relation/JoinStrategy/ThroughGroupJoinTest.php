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
            ['user_id' => 1, 'name' => 'Sumire Uesaka'],
            ['user_id' => 2, 'name' => 'Mikako Komatsu'],
            ['user_id' => 3, 'name' => 'Rumi Okubo'],
            ['user_id' => 4, 'name' => 'Natsumi Takamori'],
            ['user_id' => 5, 'name' => 'Shiori Mikami'],
        ];
        $tweets = [
            ['user_id' => 1, 'body' => 'foo'],
            ['user_id' => 1, 'body' => 'bar'],
            ['user_id' => 1, 'body' => 'baz'],
            ['user_id' => 3, 'body' => 'hoge'],
            ['user_id' => 3, 'body' => 'fuga'],
            ['user_id' => 5, 'body' => 'piyo']
        ];
        $expected = [
            [
                'user_id' => 1,
                'name' => 'Sumire Uesaka',
                'tweets' => ['foo', 'bar', 'baz'],
            ],
            [
                'user_id' => 2,
                'name' => 'Mikako Komatsu',
                'tweets' => [],
            ],
            [
                'user_id' => 3,
                'name' => 'Rumi Okubo',
                'tweets' => ['hoge', 'fuga'],
            ],
            [
                'user_id' => 4,
                'name' => 'Natsumi Takamori',
                'tweets' => [],
            ],
            [
                'user_id' => 5,
                'name' => 'Shiori Mikami',
                'tweets' => ['piyo'],
            ],
        ];

        $result = (new ThroughGroupJoin('body'))
            ->join(
                new PreloadResultSet($users, null),
                new PreloadResultSet($tweets, null),
                function($user) { return $user['user_id']; },
                function($user) { return $user['user_id']; },
                function($user, $tweets) {
                    $user['tweets'] = $tweets;
                    return $user;
                }
            );
        $this->assertEquals($expected, iterator_to_array($result));
    }
}
