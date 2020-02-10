<?php

declare(strict_types=1);

namespace Emonkak\Orm\ResultSet;

use Emonkak\Enumerable\EnumerableExtensions;

class EmptyResultSet extends \EmptyIterator implements ResultSetInterface
{
    use EnumerableExtensions;

    /**
     * @var ?class-string
     */
    private $class;

    /**
     * @param ?class-string $class
     */
    public function __construct(?string $class)
    {
        $this->class = $class;
    }

    public function getClass(): ?string
    {
        return $this->class;
    }
}
