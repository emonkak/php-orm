<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\Relation;

use Emonkak\Enumerable\EqualityComparer;
use Emonkak\Orm\Relation\JoinStrategy\LazyGroupJoin;
use Emonkak\Orm\ResultSet\PreloadedResultSet;
use PHPUnit\Framework\TestCase;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;

/**
 * @covers Emonkak\Orm\Relation\JoinStrategy\LazyGroupJoin
 */
class LazyGroupJoinTest extends TestCase
{
    public function testJoin(): void
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
        $expectedResult = [
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

        $outerKeySelector = function($user) { return $user['user_id']; };
        $innerKeySelector = function($user) { return $user['user_id']; };
        $resultSelector = function($user, $tweets) {
            $user['tweets'] = $tweets;
            return $user;
        };
        $comparer = EqualityComparer::getInstance();
        $proxyFactory = new LazyLoadingValueHolderFactory();
        $lazyGroupJoin = new LazyGroupJoin(
            $outerKeySelector,
            $innerKeySelector,
            $resultSelector,
            $comparer,
            $proxyFactory
        );

        $this->assertSame($outerKeySelector, $lazyGroupJoin->getOuterKeySelector());
        $this->assertSame($innerKeySelector, $lazyGroupJoin->getInnerKeySelector());
        $this->assertSame($resultSelector, $lazyGroupJoin->getResultSelector());
        $this->assertSame($comparer, $lazyGroupJoin->getComparer());
        $this->assertSame($proxyFactory, $lazyGroupJoin->getProxyFactory());

        $result = $lazyGroupJoin
            ->join(
                new PreloadedResultSet($users),
                new PreloadedResultSet($tweets)
            );
        $result = iterator_to_array($result, false);
        $result = array_map(function($user) {
            $user['tweets'] = $user['tweets']->getArrayCopy();
            return $user;
        }, $result);
        $this->assertEquals($expectedResult, $result);
    }
}
