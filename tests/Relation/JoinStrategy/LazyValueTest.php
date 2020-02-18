<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\Relation;

use Emonkak\Orm\Relation\JoinStrategy\LazyValue;
use PHPUnit\Framework\TestCase;

/**
 * @covers Emonkak\Orm\Relation\JoinStrategy\LazyValue
 */
class LazyValueTest extends TestCase
{
    public function testGet(): void
    {
        $key = new \stdClass();
        $value = 'foo';

        $evaluator = $this->getMockBuilder(\stdClass::class)
            ->setMethods(['__invoke'])
            ->getMock();
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
