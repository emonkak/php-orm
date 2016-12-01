<?php

namespace Emonkak\Orm\Tests\Fixtures;

class Model
{
    private $props = [];

    public function __construct(array $props)
    {
        $this->props = $props;
    }

    public function __get($key)
    {
        return $this->props[$key];
    }

    public function __set($key, $value)
    {
        $this->props[$key] = $value;
    }

    public function toArray()
    {
        return $this->props;
    }
}
