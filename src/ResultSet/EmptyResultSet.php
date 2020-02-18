<?php

declare(strict_types=1);

namespace Emonkak\Orm\ResultSet;

use Emonkak\Enumerable\EnumerableExtensions;

/**
 * @template T
 * @implements ResultSetInterface<T>
 */
class EmptyResultSet extends \EmptyIterator implements ResultSetInterface
{
    /**
     * @use EnumerableExtensions<T>
     */
    use EnumerableExtensions;
}
