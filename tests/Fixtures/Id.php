<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\Fixtures;

class Id implements \JsonSerializable
{
    private int $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function jsonSerialize(): mixed
    {
        return $this->id;
    }
}
