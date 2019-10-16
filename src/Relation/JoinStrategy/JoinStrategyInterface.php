<?php

namespace Emonkak\Orm\Relation\JoinStrategy;

use Emonkak\Orm\ResultSet\ResultSetInterface;

interface JoinStrategyInterface
{
    /**
     * @param ResultSetInterface $outer
     * @param ResultSetInterface $inner
     * @param callable           $outerKeySelector
     * @param callable           $innerKeySelector
     * @param callable           $resultSelector
     * @return \Traversable
     */
    public function join(ResultSetInterface $outer, ResultSetInterface $inner, callable $outerKeySelector, callable $innerKeySelector, callable $resultSelector);
}
