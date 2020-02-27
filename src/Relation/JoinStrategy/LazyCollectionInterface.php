<?php

declare(strict_types=1);

namespace Emonkak\Orm\Relation\JoinStrategy;

/**
 * @template TSource
 * @extends \IteratorAggregate<TSource>
 * @extends \ArrayAccess<array-key,TSource>
 * @extends LazyValueInterface<TSource[]>
 */
interface LazyCollectionInterface extends \ArrayAccess, \Countable, \IteratorAggregate, \Serializable, LazyValueInterface
{
}
