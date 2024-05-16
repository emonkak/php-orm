<?php

declare(strict_types=1);

namespace Emonkak\Orm\Relation\JoinStrategy;

/**
 * @template TValue
 */
interface LazyValueInterface
{
    /**
     * @return TValue
     */
    public function get(): mixed;
}
