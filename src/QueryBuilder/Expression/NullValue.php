<?php

namespace Emonkak\Orm\QueryBuilder\Expression;

use Emonkak\Orm\QueryBuilder\QueryBuilderInterface;
use Emonkak\Orm\QueryBuilder\ToStringable;

/**
 * @internal
 */
class NullValue implements QueryBuilderInterface
{
    use ExpressionHelper;
    use ToStringable;

    /**
     * {@inheritDoc}
     */
    public function build()
    {
        return ['NULL', []];
    }
}
