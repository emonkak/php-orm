<?php

declare(strict_types=1);

namespace Emonkak\Orm\Relation\JoinStrategy;

/**
 * @template T
 */
class LazyValue
{
    /**
     * @psalm-var T
     */
    private $value;

    /**
     * @psalm-param T $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @psalm-return T
     */
    public function get()
    {
        return $this->value;
    }
}
