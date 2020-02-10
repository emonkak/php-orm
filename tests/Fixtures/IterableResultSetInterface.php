<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\Fixtures;

use Emonkak\Orm\ResultSet\ResultSetInterface;;

interface IterableResultSetInterface extends \IteratorAggregate, ResultSetInterface
{
}
