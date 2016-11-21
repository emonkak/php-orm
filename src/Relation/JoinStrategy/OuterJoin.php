<?php

namespace Emonkak\Orm\Relation\JoinStrategy;

use Emonkak\Enumerable\Iterator\OuterJoinIterator;
use Emonkak\Orm\ResultSet\ResultSetInterface;

class OuterJoin implements JoinStrategyInterface
{
    /**
     * {@inheritDoc}
     */
    public function join(ResultSetInterface $outer, ResultSetInterface $inner, callable $outerKeySelector, callable $innerKeySelector, callable $resultSelector)
    {
        return new OuterJoinIterator(
            $outer,
            $inner,
            $outerKeySelector,
            $innerKeySelector,
            $resultSelector
        );
    }
}
