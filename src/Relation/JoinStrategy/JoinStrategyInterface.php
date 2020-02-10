<?php

declare(strict_types=1);

namespace Emonkak\Orm\Relation\JoinStrategy;

use Emonkak\Orm\ResultSet\ResultSetInterface;

interface JoinStrategyInterface
{
    public function join(ResultSetInterface $outer, ResultSetInterface $inner, callable $outerKeySelector, callable $innerKeySelector, callable $resultSelector): \Traversable;
}
