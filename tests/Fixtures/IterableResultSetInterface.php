<?php

namespace Emonkak\Orm\Tests\Fixtures;

use Emonkak\Orm\ResultSet\ResultSetInterface;;

interface IterableResultSetInterface extends \IteratorAggregate, ResultSetInterface
{
}
