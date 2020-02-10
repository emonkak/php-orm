<?php

declare(strict_types=1);

namespace Emonkak\Orm\Relation\JoinStrategy;

use Emonkak\Enumerable\EqualityComparer;
use Emonkak\Enumerable\Iterator\OuterJoinIterator;
use Emonkak\Orm\Relation\AccessorCreators;
use Emonkak\Orm\ResultSet\ResultSetInterface;

class ThroughOuterJoin implements JoinStrategyInterface
{
    /**
     * @var string
     */
    private $throughKey;

    public function __construct(string $throughKey)
    {
        $this->throughKey = $throughKey;
    }

    public function join(ResultSetInterface $outer, ResultSetInterface $inner, callable $outerKeySelector, callable $innerKeySelector, callable $resultSelector): \Traversable
    {
        $throughKeySelector = AccessorCreators::createKeySelector($this->throughKey, $inner->getClass());
        return new OuterJoinIterator(
            $outer,
            $inner,
            $outerKeySelector,
            $innerKeySelector,
            static function($lhs, $rhs) use ($resultSelector, $throughKeySelector) {
                return $resultSelector($lhs, $rhs !== null ? $throughKeySelector($rhs) : null);
            },
            EqualityComparer::getInstance()
        );
    }
}
