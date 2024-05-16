<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\Relation;

use Emonkak\Orm\Relation\JoinStrategy\LazyCollection;
use Emonkak\Orm\Tests\Fixtures\Spy;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Emonkak\Orm\Relation\JoinStrategy\LazyCollection
 */
class LazyCollectionTest extends TestCase
{
    /**
     * @psalm-suppress DocblockTypeContradiction
     */
    public function testGet(): void
    {
        $key = new \stdClass();
        $source = ['foo', 'bar', 'baz'];

        $spy = $this->createMock(Spy::class);
        $spy
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($key))
            ->willReturn($source);

        /** @var LazyCollection<string,\stdClass> */
        $collection = new LazyCollection(
            $key,
            /**
             * @return string[]
             */
            function(\stdClass $key) use ($spy): array {
                return $spy->__invoke($key);
            });

        $this->assertSame($source, $collection->get());
        $this->assertSame($source, iterator_to_array($collection));
        $this->assertSame($source, unserialize(serialize($collection))->get());
        $this->assertCount(count($source), $collection);
        $this->assertTrue(isset($collection[0]));
        $this->assertTrue(isset($collection[1]));
        $this->assertTrue(isset($collection[2]));
        $this->assertSame('foo', $collection[0]);
        $this->assertSame('bar', $collection[1]);
        $this->assertSame('baz', $collection[2]);

        $collection[0] = 'qux';
        $collection[1] = 'quux';
        $collection[2] = 'quuz';
        $collection[] = 'gorge';

        $this->assertSame('qux', $collection[0]);
        $this->assertSame('quux', $collection[1]);
        $this->assertSame('quuz', $collection[2]);
        $this->assertSame('gorge', $collection[3]);

        unset($collection[0]);
        unset($collection[1]);
        unset($collection[2]);
        unset($collection[3]);

        $this->assertSame([], $collection->get());
        $this->assertSame([], iterator_to_array($collection));
        $this->assertSame([], unserialize(serialize($collection))->get());
        $this->assertCount(0, $collection);
        $this->assertFalse(isset($collection[0]));
        $this->assertFalse(isset($collection[1]));
        $this->assertFalse(isset($collection[2]));
    }

    public function testSerialize(): void
    {
        $xs = new LazyCollection('foo', function(): array {
            return [1, 2, 3];
        });
        $this->assertSame([1, 2, 3], unserialize(serialize($xs))->get());
    }
}
