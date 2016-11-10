<?php

namespace Emonkak\Orm\QueryBuilder\Expression;

use Emonkak\Orm\QueryBuilder\QueryBuilderInterface;
use Emonkak\Orm\QueryBuilder\Stringable;

/**
 * @internal
 */
class NullValue implements QueryBuilderInterface
{
    use ExpressionHelper;
    use Stringable;

    /**
     * {@inheritDoc}
     */
    public function build()
    {
        return ['NULL', []];
    }
}
