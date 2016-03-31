<?php

namespace Emonkak\Orm;

use Emonkak\Orm\QueryBuilder\PlainQueryBuilder;

class PlainQuery extends PlainQueryBuilder implements ExecutableQueryInterface
{
    use Executable;
}
