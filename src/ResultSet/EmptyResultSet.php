<?php

declare(strict_types=1);

namespace Emonkak\Orm\ResultSet;

use Emonkak\Enumerable\EnumerableExtensions;

/**
 * @implements ResultSetInterface<mixed>
 */
class EmptyResultSet extends \EmptyIterator implements ResultSetInterface
{
    use EnumerableExtensions;
}
