<?php

namespace Emonkak\Orm\Relation\JoinStrategy;

use Emonkak\Enumerable\Iterator\OuterJoinIterator;
use Emonkak\Orm\Relation\AccessorCreators;
use Emonkak\Orm\ResultSet\ResultSetInterface;

class ThroughOuterJoin implements JoinStrategyInterface
{
    /**
     * @var string
     */
    private $throughKey;

    /**
     * @param string $throughKey
     */
    public function __construct($throughKey)
    {
        $this->throughKey = $throughKey;
    }

    /**
     * {@inheritDoc}
     */
    public function join(ResultSetInterface $outer, ResultSetInterface $inner, callable $outerKeySelector, callable $innerKeySelector, callable $resultSelector)
    {
        $throughKeySelector = AccessorCreators::toKeySelector($this->throughKey, $inner->getClass());
        return new OuterJoinIterator(
            $outer,
            $inner,
            $outerKeySelector,
            $innerKeySelector,
            static function($lhs, $rhs) use ($resultSelector, $throughKeySelector) {
                return $resultSelector($lhs, $throughKeySelector($rhs));
            }
        );
    }
}
