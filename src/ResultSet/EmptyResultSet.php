<?php

declare(strict_types=1);

namespace Emonkak\Orm\ResultSet;

use Emonkak\Enumerable\EnumerableExtensions;

/**
 * @implements ResultSetInterface<mixed>
 * @use EnumerableExtensions<mixed>
 */
class EmptyResultSet extends \EmptyIterator implements ResultSetInterface
{
    use EnumerableExtensions;
}
