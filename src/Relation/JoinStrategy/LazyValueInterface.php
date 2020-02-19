<?php

declare(strict_types=1);

namespace Emonkak\Orm\Relation\JoinStrategy;

/**
 * @template TValue
 */
interface LazyValueInterface extends \Serializable
{
    /**
     * @psalm-return TValue
     */
    public function get();
}
