<?php

declare(strict_types=1);

namespace Emonkak\Orm\ResultSet;

use Emonkak\Enumerable\EnumerableExtensions;

/**
 * @template T
 * @implements \IteratorAggregate<T>
 * @implements ResultSetInterface<T>
 */
class PreloadedResultSet implements \IteratorAggregate, ResultSetInterface
{
    /**
     * @use EnumerableExtensions<T>
     */
    use EnumerableExtensions;

    /**
     * @var T[]
     */
    private array $elements;

    /**
     * @param T[] $elements
     */
    public function __construct(array $elements)
    {
        $this->elements = $elements;
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->elements);
    }

    public function getSource(): iterable
    {
        return $this->elements;
    }
}
