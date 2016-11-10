<?php

namespace Emonkak\Orm\Relation\JoinStrategy;

interface JoinStrategyInterface
{
    public function __invoke($outer, $inner, callable $outerKeySelector, callable $innerKeySelector, callable $resultSelector);
}
