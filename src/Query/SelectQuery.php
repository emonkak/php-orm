<?php

namespace Emonkak\Orm\Query;

use Emonkak\QueryBuilder\SelectBuilder;

class SelectQuery extends SelectBuilder implements ExecutableQueryInterface
{
    use Executable;
}
