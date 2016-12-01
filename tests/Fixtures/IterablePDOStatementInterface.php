<?php

namespace Emonkak\Orm\Tests\Fixtures;

use Emonkak\Database\PDOStatementInterface;

interface IterablePDOStatementInterface extends \IteratorAggregate, PDOStatementInterface
{
}
