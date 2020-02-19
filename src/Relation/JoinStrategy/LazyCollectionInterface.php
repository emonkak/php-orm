<?php

declare(strict_types=1);

namespace Emonkak\Orm\Relation\JoinStrategy;

/**
 * @template TSource
 * @extends \IteratorAggregate<TSource>
 * @extends \ArrayAccess<array-key,TSource>
 */
interface LazyCollectionInterface extends \ArrayAccess, \Countable, \IteratorAggregate, \Serializable
{
    /**
     * @psalm-return TSource[]
     */
    public function get(): array;
}
