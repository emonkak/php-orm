<?php

namespace Emonkak\Orm\Tests\Fixtures;

class Id implements \JsonSerializable
{
    private $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function jsonSerialize()
    {
        return $this->id;
    }
}
