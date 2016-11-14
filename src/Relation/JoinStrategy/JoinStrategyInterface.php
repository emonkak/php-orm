<?php

namespace Emonkak\Orm\Relation\JoinStrategy;

interface JoinStrategyInterface
{
    /**
     * @param array|\Traversable $outer
     * @param array|\Traversable $inner
     * @param callable           $outerKeySelector
     * @param callable           $innerKeySelector
     * @param callable           $resultSelector
     * @return Traversable
     */
    public function __invoke($outer, $inner, callable $outerKeySelector, callable $innerKeySelector, callable $resultSelector);
}
