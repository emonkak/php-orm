<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\Fixtures;

class Entity
{
    private $foo;

    public function getFoo()
    {
        return $this->foo;
    }

    public function setFoo($foo)
    {
        $this->foo = $foo;
    }
}
