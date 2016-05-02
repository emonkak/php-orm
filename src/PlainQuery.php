<?php

namespace Emonkak\Orm;

use Emonkak\Orm\QueryBuilder\PlainQueryBuilder;

class PlainQuery extends PlainQueryBuilder implements ExecutableQueryInterface
{
    use Executable, Observable {
        Observable::execute insteadof Executable;
        Observable::getResult insteadof Executable;
        Executable::execute as executeWithoutObservers;
        Executable::getResult as getResultWithoutObservers;
    }
    use Relatable;
}
