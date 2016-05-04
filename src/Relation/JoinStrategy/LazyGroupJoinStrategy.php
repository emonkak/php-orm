<?php

namespace Emonkak\Orm\Relation\JoinStrategy;

use Emonkak\Enumerable\Iterator\MemoizeIterator;
use Emonkak\Enumerable\Iterator\SelectIterator;
use Emonkak\Enumerable\Iterator\WhereIterator;

/**
 * @internal
 */
class LazyGroupJoinStrategy
{
    /**
     * {@inheritDoc}
     */
    public function __invoke($outer, $inner, callable $outerKeySelector, callable $innerKeySelector, callable $resultSelector)
    {
        $innerElements = new MemoizeIterator($inner);

        return new SelectIterator($outer, static function($outerElement) use ($innerElements, $outerKeySelector, $innerKeySelector, $resultSelector) {
            $innerElements = new WhereIterator($innerElements, static function($innerElement) use ($outerElement, $outerKeySelector, $innerKeySelector) {
                return $outerKeySelector($outerElement) === $innerKeySelector($innerElement);
            });
            $innerElements = new MemoizeIterator($innerElements);
            return $resultSelector($outerElement, $innerElements);
        });
    }
}