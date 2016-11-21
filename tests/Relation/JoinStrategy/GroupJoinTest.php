<?php

namespace Emonkak\Orm\Tests\Relation;

use Emonkak\Orm\Relation\JoinStrategy\GroupJoin;
use Emonkak\Orm\ResultSet\FrozenResultSet;

/**
 * @covers Emonkak\Orm\Relation\JoinStrategy\GroupJoin
 */
class GroupJoinTest extends \PHPUnit_Framework_TestCase
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
            ['user_id' => 5, 'body' => 'piyo'],
        ];
        $expected = [
            [
                'user_id' => 1,
                'name' => 'Sumire Uesaka',
                'tweets' => [$tweets[0], $tweets[1], $tweets[2]],
            ],
            [
                'user_id' => 2,
                'name' => 'Mikako Komatsu',
                'tweets' => [],
            ],
            [
                'user_id' => 3,
                'name' => 'Rumi Okubo',
                'tweets' => [$tweets[3], $tweets[4]],
            ],
            [
                'user_id' => 4,
                'name' => 'Natsumi Takamori',
                'tweets' => [],
            ],
            [
                'user_id' => 5,
                'name' => 'Shiori Mikami',
                'tweets' => [$tweets[5]],
            ],
        ];

        $result = (new GroupJoin())
            ->join(
                new FrozenResultSet($users, null),
                new FrozenResultSet($tweets, null),
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
