<?php

declare(strict_types=1);

namespace Emonkak\Orm\ResultSet;

use Emonkak\Enumerable\EnumerableExtensions;

/**
 * @template T
 * @implements \IteratorAggregate<T>
 * @implements ResultSetInterface<T>
 * @use EnumerableExtensions<T>
 */
class PreloadedResultSet implements \IteratorAggregate, ResultSetInterface
{
    use EnumerableExtensions;

    /**
     * @psalm-var T[]
     * @var mixed[]
     */
    private $elements;

    /**
     * @psalm-param T[] $elements
     */
    public function __construct(array $elements)
    {
        $this->elements = $elements;
    }

    /**
     * @psalm-return \Traversable<T>
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->elements);
    }

    /**
     * {@inheritDoc}
     */
    public function getSource(): iterable
    {
        return $this->elements;
    }
}
