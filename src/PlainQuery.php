<?php

namespace Emonkak\Orm;

use Emonkak\QueryBuilder\PlainQueryBuilder;
use Emonkak\QueryBuilder\Chainable;

class PlainQuery extends PlainQueryBuilder implements ExecutableQueryInterface
{
    use Chainable;
    use Executable, Observable {
        Observable::execute insteadof Executable;
        Executable::execute as executeWithoutObservers;
    }
}
