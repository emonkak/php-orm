<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests;

class ToStringable
{
    private $string;

    public function __construct($string)
    {
        $this->string = $string;
    }

    public function __toString()
    {
        return $this->string;
    }
}
