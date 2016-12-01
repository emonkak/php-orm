<?php

namespace Emonkak\Orm\ResultSet;

use Emonkak\Enumerable\EnumerableExtensions;

class PreloadResultSet implements \IteratorAggregate, ResultSetInterface
{
    use EnumerableExtensions;

    /**
     * @var mixed[]
     */
    private $elements;

    /**
     * @var string
     */
    private $class;

    /**
     * @param mixed[] $elements
     * @param string  $class
     */
    public function __construct(array $elements, $class)
    {
        $this->elements = $elements;
        $this->class = $class;
    }

    /**
     * {@inheritDoc}
     */
    public function getIterator()
    {
        return new \ArrayIterator($this->elements);
    }

    /**
     * {@inheritDoc}
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * {@inheritDoc}
     */
    public function getSource()
    {
        return $this->elements;
    }
}
