<?php

declare(strict_types=1);

namespace Emonkak\Orm\Relation\JoinStrategy;

use Emonkak\Enumerable\EqualityComparer;
use Emonkak\Enumerable\Iterator\OuterJoinIterator;
use Emonkak\Orm\ResultSet\ResultSetInterface;

class OuterJoin implements JoinStrategyInterface
{
    public function join(ResultSetInterface $outer, ResultSetInterface $inner, callable $outerKeySelector, callable $innerKeySelector, callable $resultSelector): \Traversable
    {
        return new OuterJoinIterator(
            $outer,
            $inner,
            $outerKeySelector,
            $innerKeySelector,
            $resultSelector,
            EqualityComparer::getInstance()
        );
    }
}
