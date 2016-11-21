<?php

namespace Emonkak\Orm\Relation\JoinStrategy;

use Emonkak\Enumerable\Iterator\GroupJoinIterator;
use Emonkak\Orm\ResultSet\ResultSetInterface;

class GroupJoin implements JoinStrategyInterface
{
    /**
     * {@inheritDoc}
     */
    public function join(ResultSetInterface $outer, ResultSetInterface $inner, callable $outerKeySelector, callable $innerKeySelector, callable $resultSelector)
    {
        return new GroupJoinIterator(
            $outer,
            $inner,
            $outerKeySelector,
            $innerKeySelector,
            $resultSelector
        );
    }
}
