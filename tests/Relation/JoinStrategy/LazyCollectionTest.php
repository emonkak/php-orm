<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\Relation;

use Emonkak\Orm\Relation\JoinStrategy\LazyCollection;
use PHPUnit\Framework\TestCase;

/**
 * @covers Emonkak\Orm\Relation\JoinStrategy\LazyCollection
 */
class LazyCollectionTest extends TestCase
{
    public function testGet(): void
    {
        $key = new \stdClass();
        $source = ['foo', 'bar', 'baz'];

        $evaluator = $this->getMockBuilder(\stdClass::class)
            ->setMethods(['__invoke'])
            ->getMock();
        $evaluator
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($key))
            ->willReturn($source);

        $lazyCollection = new LazyCollection($key, $evaluator);

        $this->assertSame($source, $lazyCollection->get());
        $this->assertSame($source, iterator_to_array($lazyCollection));
        $this->assertSame($source, unserialize(serialize($lazyCollection))->get());
        $this->assertCount(count($source), $lazyCollection);
        $this->assertTrue(isset($lazyCollection[0]));
        $this->assertTrue(isset($lazyCollection[1]));
        $this->assertTrue(isset($lazyCollection[2]));
        $this->assertSame($source[0], $lazyCollection[0]);
        $this->assertSame($source[1], $lazyCollection[1]);
        $this->assertSame($source[2], $lazyCollection[2]);

        $lazyCollection[0] = 'qux';
        $lazyCollection[1] = 'quux';
        $lazyCollection[2] = 'quuz';
        $lazyCollection[] = 'gorge';

        $this->assertSame('qux', $lazyCollection[0]);
        $this->assertSame('quux', $lazyCollection[1]);
        $this->assertSame('quuz', $lazyCollection[2]);
        $this->assertSame('gorge', $lazyCollection[3]);

        unset($lazyCollection[0]);
        unset($lazyCollection[1]);
        unset($lazyCollection[2]);
        unset($lazyCollection[3]);

        $this->assertSame([], $lazyCollection->get());
        $this->assertSame([], iterator_to_array($lazyCollection));
        $this->assertSame([], unserialize(serialize($lazyCollection))->get());
        $this->assertCount(0, $lazyCollection);
        $this->assertFalse(isset($lazyCollection[0]));
        $this->assertFalse(isset($lazyCollection[1]));
        $this->assertFalse(isset($lazyCollection[2]));
    }
}
