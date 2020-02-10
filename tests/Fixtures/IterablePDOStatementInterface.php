<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\Fixtures;

use Emonkak\Database\PDOStatementInterface;

interface IterablePDOStatementInterface extends \IteratorAggregate, PDOStatementInterface
{
}
