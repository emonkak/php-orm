<?php

namespace Emonkak\Orm\Relation\JoinStrategy;

use Emonkak\Enumerable\Iterator\GroupJoinIterator;
use Emonkak\Orm\Relation\AccessorCreators;
use Emonkak\Orm\ResultSet\ResultSetInterface;

class ThroughGroupJoin implements JoinStrategyInterface
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
        $throughKeySelector = AccessorCreators::createKeySelector($this->throughKey, $inner->getClass());
        return new GroupJoinIterator(
            $outer,
            $inner,
            $outerKeySelector,
            $innerKeySelector,
            static function($lhs, $rhs) use ($resultSelector, $throughKeySelector) {
                return $resultSelector($lhs, array_map($throughKeySelector, $rhs));
            }
        );
    }
}
