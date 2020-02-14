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
        $this->assertSame(123, (new LazyValue(123))->get());
    }
}
