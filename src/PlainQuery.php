<?php

namespace Emonkak\Orm;

use Emonkak\QueryBuilder\PlainQueryBuilder;
use Emonkak\QueryBuilder\QueryBuilderInterface;
use Emonkak\QueryBuilder\ToStringable;
use Emonkak\QueryBuilder\Chainable;

class PlainQuery extends PlainQueryBuilder implements ExecutableQueryInterface
{
    use Chainable;
    use Executable;
    use ToStringable;
}
