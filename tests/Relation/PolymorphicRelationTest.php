<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\Relation;

use Emonkak\Orm\Relation\PolymorphicRelation;
use Emonkak\Orm\Relation\RelationInterface;
use Emonkak\Orm\ResultSet\PreloadedResultSet;
use PHPUnit\Framework\TestCase;

/**
 * @covers Emonkak\Orm\Relation\PolymorphicRelation
 */
class PolymorphicRelationTest extends TestCase
{
    public function testConstructor()
    {
        $polymorphics = [
            'morph_key1' => $this->createMock(RelationInterface::class),
            'morph_key2' => $this->createMock(RelationInterface::class),
        ];

        $relation = new PolymorphicRelation(
            'morph_key',
            $polymorphics
        );

        $this->assertSame('morph_key', $relation->getMorphKey());
        $this->assertSame($polymorphics, $relation->getPolymorphics());
    }

    public function testAssociate()
    {
        $comments = [
            ['comment_id' => 1, 'commentable_id' => 1, 'commentable_type' => 'posts', 'body' => 'foo'],
            ['comment_id' => 2, 'commentable_id' => 2, 'commentable_type' => 'videos', 'body' => 'bar'],
            ['comment_id' => 3, 'commentable_id' => 3, 'commentable_type' => 'posts', 'body' => 'baz'],
            ['comment_id' => 4, 'commentable_id' => null, 'commentable_type' => null, 'body' => 'qux'],
        ];
        $posts = [
            ['post_id' => 1, 'content' => 'foo'],
            ['post_id' => 2, 'content' => 'bar'],
            ['post_id' => 3, 'content' => 'baz'],
        ];
        $videos = [
            ['video_id' => 1, 'title' => 'foo'],
            ['video_id' => 2, 'title' => 'bar'],
            ['video_id' => 3, 'title' => 'baz'],
        ];
        $expectedResult = [
            [
                'comment_id' => 1,
                'commentable_id' => 1,
                'commentable_type' => 'posts',
                'commentable' => [
                    'post_id' => 1,
                    'content' => 'foo',
                ],
                'body' => 'foo',
            ],
            [
                'comment_id' => 2,
                'commentable_id' => 2,
                'commentable_type' => 'videos',
                'commentable' => [
                    'video_id' => 2,
                    'title' => 'bar',
                ],
                'body' => 'bar',
            ],
            [
                'comment_id' => 3,
                'commentable_id' => 3,
                'commentable_type' => 'posts',
                'commentable' => [
                    'post_id' => 3,
                    'content' => 'baz',
                ],
                'body' => 'baz',
            ],
            [
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
            ->with(new PreloadedResultSet([
                $comments[0] + ['__sort' => 0],
                $comments[2] + ['__sort' => 2]
            ], null))
            ->will($this->returnCallback(function($result) use ($posts) {
                return new \ArrayIterator([
                     $result->elementAt(0) + ['commentable' => $posts[0]],
                     $result->elementAt(1) + ['commentable' => $posts[2]],
                ]);
            }));

        $hasVideo = $this->createMock(RelationInterface::class);
        $hasVideo
            ->expects($this->once())
            ->method('associate')
            ->with(new PreloadedResultSet([
                 $comments[1] + ['__sort' => 1]
            ], null))
            ->will($this->returnCallback(function($result) use ($videos) {
                return new \ArrayIterator([
                     $result->elementAt(0) + ['commentable' => $videos[1]],
                ]);
            }));

        $polymorphics = [
            'videos' => $hasVideo,
            'posts' => $hasPost,
        ];

        $relation = new PolymorphicRelation(
            'commentable_type',
            $polymorphics
        );

        $result = $relation->associate(new PreloadedResultSet($comments, null));
        $this->assertEquals($expectedResult, iterator_to_array($result));
    }
}
