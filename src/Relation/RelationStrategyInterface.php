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
     * @psalm-param TKey[] $outerKeys
     * @psalm-param JoinStrategyInterface<TOuter,TInner,TKey,TResult> $joinStrategy
     * @psalm-return iterable<TInner>
     */
    public function getResult(array $outerKeys, JoinStrategyInterface $joinStrategy): iterable;
}
