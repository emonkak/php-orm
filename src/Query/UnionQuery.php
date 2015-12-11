<?php

namespace Emonkak\Orm\Query;

use Emonkak\QueryBuilder\UnionQueryBuilder;

class UnionQuery extends UnionQueryBuilder implements ExecutableQueryInterface
{
    use Executable, Observable {
        Observable::execute insteadof Executable;
    }
}
