<?php

namespace Emonkak\Orm\Tests\Relation;

use Emonkak\Orm\Relation\Polymorphic;
use Emonkak\Orm\Relation\RelationInterface;
use Emonkak\Orm\ResultSet\PreloadResultSet;

/**
 * @covers Emonkak\Orm\Relation\Polymorphic
 */
class PolymorphicTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $polymorphics = [
            'morph_key1' => $this->createMock(RelationInterface::class),
            'morph_key2' => $this->createMock(RelationInterface::class),
        ];

        $relation = new Polymorphic(
            'morph_key',
            $polymorphics
        );

        $this->assertSame('morph_key', $relation->getMorphKey());
        $this->assertSame($polymorphics, $relation->getPolymorphics());
    }

    public function testAssociate()
    {
        $comments = [
            (object) ['comment_id' => 1, 'commentable_id' => 1, 'commentable_type' => 'posts', 'body' => 'foo'],
            (object) ['comment_id' => 2, 'commentable_id' => 2, 'commentable_type' => 'videos', 'body' => 'bar'],
            (object) ['comment_id' => 3, 'commentable_id' => 3, 'commentable_type' => 'posts', 'body' => 'baz'],
            (object) ['comment_id' => 4, 'commentable_id' => null, 'commentable_type' => null, 'body' => 'qux'],
        ];
        $posts = [
            (object) ['post_id' => 1, 'content' => 'foo'],
            (object) ['post_id' => 2, 'content' => 'bar'],
            (object) ['post_id' => 3, 'content' => 'baz'],
        ];
        $videos = [
            (object) ['video_id' => 1, 'title' => 'foo'],
            (object) ['video_id' => 2, 'title' => 'bar'],
            (object) ['video_id' => 3, 'title' => 'baz'],
        ];
        $expectedResult = [
            (object) [
                'comment_id' => 1,
                'commentable_id' => 1,
                'commentable_type' => 'posts',
                'commentable' => (object) [
                    'post_id' => 1,
                    'content' => 'foo',
                ],
                'body' => 'foo',
            ],
            (object) [
                'comment_id' => 2,
                'commentable_id' => 2,
                'commentable_type' => 'videos',
                'commentable' => (object) [
                    'video_id' => 2,
                    'title' => 'bar',
                ],
                'body' => 'bar',
            ],
            (object) [
                'comment_id' => 3,
                'commentable_id' => 3,
                'commentable_type' => 'posts',
                'commentable' => (object) [
                    'post_id' => 3,
                    'content' => 'baz',
                ],
                'body' => 'baz',
            ],
            (object) [
                'comment_id' => 4,
                'commentable_id' => null,
                'commentable_type' => null,
                'body' => 'qux',
            ],
        ];

        $hasPost = $this->createMock(RelationInterface::class);
        $hasPost
            ->expects($this->once())
            ->method('associate')
            ->with(new PreloadResultSet([
                (object) ((array) $comments[0] + ['__sort' => 0]),
                (object) ((array) $comments[2] + ['__sort' => 2])
            ], null))
            ->will($this->returnCallback(function($result) use ($posts) {
                return new \ArrayIterator([
                    (object) ((array) $result->elementAt(0) + ['commentable' => $posts[0]]),
                    (object) ((array) $result->elementAt(1) + ['commentable' => $posts[2]]),
                ]);
            }));

        $hasVideo = $this->createMock(RelationInterface::class);
        $hasVideo
            ->expects($this->once())
            ->method('associate')
            ->with(new PreloadResultSet([
                (object) ((array) $comments[1] + ['__sort' => 1])
            ], null))
            ->will($this->returnCallback(function($result) use ($videos) {
                return new \ArrayIterator([
                    (object) ((array) $result->elementAt(0) + ['commentable' => $videos[1]]),
                ]);
            }));

        $polymorphics = [
            'videos' => $hasVideo,
            'posts' => $hasPost,
        ];

        $relation = new Polymorphic(
            'commentable_type',
            $polymorphics
        );

        $result = $relation->associate(new PreloadResultSet($comments, null));
        $this->assertEquals($expectedResult, iterator_to_array($result));
    }
}
