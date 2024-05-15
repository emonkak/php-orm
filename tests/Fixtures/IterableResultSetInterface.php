<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\Fixtures;

use Emonkak\Orm\ResultSet\ResultSetInterface;

/**
 * @extends \IteratorAggregate<mixed,mixed>
 * @extends ResultSetInterface<mixed>
 */
interface IterableResultSetInterface extends \IteratorAggregate, ResultSetInterface
{
}
