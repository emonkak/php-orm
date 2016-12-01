<?php

namespace Emonkak\Orm\Tests\Fixtures;

use Emonkak\Database\PDOStatementInterface;

interface MockedPDOStatementInterface extends \IteratorAggregate, PDOStatementInterface
{
}
