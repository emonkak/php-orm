<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\Fixtures;

class Entity
{
    private mixed $foo = null;

    public mixed $__foo = null;

    public mixed $__pivot_foo = null;

    public function getFoo(): mixed
    {
        return $this->foo;
    }

    public function setFoo(mixed $foo): void
    {
        $this->foo = $foo;
    }
}
