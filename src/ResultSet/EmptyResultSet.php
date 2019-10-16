<?php

namespace Emonkak\Orm\ResultSet;

use Emonkak\Enumerable\EnumerableExtensions;

class EmptyResultSet extends \EmptyIterator implements ResultSetInterface
{
    use EnumerableExtensions;

    /**
     * @var string
     */
    private $class;

    /**
     * @param class-string $class
     */
    public function __construct($class)
    {
        $this->class = $class;
    }

    /**
     * {@inheritDoc}
     */
    public function getClass()
    {
        return $this->class;
    }
}
