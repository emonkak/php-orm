<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\Fixtures;

use Emonkak\Database\PDOStatementInterface;

/**
 * @extends \IteratorAggregate<mixed,mixed>
 */
interface IterablePDOStatementInterface extends \IteratorAggregate, PDOStatementInterface
{
}
