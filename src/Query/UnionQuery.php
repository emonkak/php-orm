<?php

namespace Emonkak\Orm\Query;

use Emonkak\QueryBuilder\UnionQueryBuilder;

class UnionQuery extends UnionQueryBuilder implements QueryInterface
{
    use Executable;
}
