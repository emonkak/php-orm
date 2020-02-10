<?php

declare(strict_types=1);

namespace Emonkak\Orm\ResultSet;

use Emonkak\Enumerable\EnumerableExtensions;

class PreloadedResultSet implements \IteratorAggregate, ResultSetInterface
{
    use EnumerableExtensions;

    /**
     * @var mixed[]
     */
    private $elements;

    /**
     * @var ?class-string
     */
    private $class;

    /**
     * @param mixed[] $elements
     * @param ?class-string $class
     */
    public function __construct(array $elements, ?string $class)
    {
        $this->elements = $elements;
        $this->class = $class;
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->elements);
    }

    public function getClass(): ?string
    {
        return $this->class;
    }

    public function getSource(): iterable
    {
        return $this->elements;
    }
}
