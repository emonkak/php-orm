<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\Relation;

use Emonkak\Orm\Relation\JoinStrategy\LazyValue;
use Emonkak\Orm\Tests\Fixtures\Spy;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Emonkak\Orm\Relation\JoinStrategy\LazyValue
 */
class LazyValueTest extends TestCase
{
    public function testGet(): void
    {
        $key = new \stdClass();
        $value = 'foo';

        $spy = $this->createMock(Spy::class);
        $spy
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($key))
            ->willReturn($value);

        $lazyValue = new LazyValue($key, function(\stdClass $key) use ($spy): string {
            return $spy->__invoke($key);
        });

        $this->assertSame($value, $lazyValue->get());
        $this->assertSame($value, $lazyValue->get());
        $this->assertSame($value, unserialize(serialize($lazyValue))->get());
    }

    public function testSerialize(): void
    {
        $value = new LazyValue('foo', function(): int {
            return 123;
        });
        $this->assertSame(123, unserialize(serialize($value))->get());
    }
}
