<?php

namespace Emonkak\Orm\QueryBuilder\Expression;

use Emonkak\Orm\QueryBuilder\QueryFragmentInterface;

/**
 * @internal
 */
class NullValue implements QueryFragmentInterface
{
    use ExpressionHelper;

    /**
     * {@inheritDoc}
     */
    public function build()
    {
        return ['NULL', []];
    }
}
