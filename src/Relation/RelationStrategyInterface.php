<?php

declare(strict_types=1);

namespace Emonkak\Orm\Relation;

use Emonkak\Orm\Relation\JoinStrategy\JoinStrategyInterface;

/**
 * @template TInner
 * @template TKey
 */
interface RelationStrategyInterface
{
    /**
     * @template TOuter
     * @template TResult
     * @param TKey[] $outerKeys
     * @param JoinStrategyInterface<TOuter,TInner,TKey,TResult> $joinStrategy
     * @return iterable<TInner>
     */
    public function getResult(array $outerKeys, JoinStrategyInterface $joinStrategy): iterable;
}
