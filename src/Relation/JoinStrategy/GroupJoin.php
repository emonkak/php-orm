<?php

declare(strict_types=1);

namespace Emonkak\Orm\Relation\JoinStrategy;

use Emonkak\Enumerable\Iterator\GroupJoinIterator;
use Emonkak\Enumerable\EqualityComparer;
use Emonkak\Orm\ResultSet\ResultSetInterface;

class GroupJoin implements JoinStrategyInterface
{
    public function join(ResultSetInterface $outer, ResultSetInterface $inner, callable $outerKeySelector, callable $innerKeySelector, callable $resultSelector): \Traversable
    {
        return new GroupJoinIterator(
            $outer,
            $inner,
            $outerKeySelector,
            $innerKeySelector,
            $resultSelector,
            EqualityComparer::getInstance()
        );
    }
}
