<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\Relation;

use Emonkak\Orm\Relation\JoinStrategy\LazyValue;
use Emonkak\Orm\Tests\Fixtures\Spy;;
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

        $evaluator = $this->createMock(Spy::class);
        $evaluator
            ->expects($this->once())
            ->method('__invoke')
            ->with($this->identicalTo($key))
            ->willReturn($value);

        $lazyValue = new LazyValue($key, $evaluator);

        $this->assertSame($value, $lazyValue->get());
        $this->assertSame($value, $lazyValue->get());
        $this->assertSame($value, unserialize(serialize($lazyValue))->get());
    }
}
