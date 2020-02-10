<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\Pagination;

use Emonkak\Orm\Pagination\PageInterface;

interface MockedPage extends \IteratorAggregate, PageInterface
{
}
