<?php

namespace Emonkak\Orm\Relation\JoinStrategy;

use Emonkak\Enumerable\Iterator\GroupJoinIterator;

/**
 * @internal
 */
class GroupJoinStrategy
{
    /**
     * {@inheritDoc}
     */
    public function __invoke($outer, $inner, callable $outerKeySelector, callable $innerKeySelector, callable $resultSelector)
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
