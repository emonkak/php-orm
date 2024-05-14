<?php

declare(strict_types=1);

namespace Emonkak\Orm\Relation\JoinStrategy;

/**
 * @template TValue
 */
interface LazyValueInterface
{
    /**
     * @psalm-return TValue
     */
    public function get();
}
