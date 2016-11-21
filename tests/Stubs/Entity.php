<?php

namespace Emonkak\Orm\Tests\Stubs;

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
