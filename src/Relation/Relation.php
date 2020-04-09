<?php

declare(strict_types=1);

namespace Emonkak\Orm\Relation;

use Emonkak\Orm\Relation\JoinStrategy\JoinStrategyInterface;

/**
 * @template TOuter
 * @template TInner
 * @template TKey
 * @template TResult
 * @implements RelationInterface<TOuter,TResult>
 */
class Relation implements RelationInterface
{
    /**
     * @psalm-var ?class-string<TResult>
     * @var ?class-string
     */
    private $resultClass;

    /**
     * @psalm-var RelationStrategyInterface<TInner,TKey>
     * @var RelationStrategyInterface
     */
    private $relationStrategy;

    /**
     * @psalm-var JoinStrategyInterface<TOuter,TInner,TKey,TResult>
     * @var JoinStrategyInterface
     */
    private $joinStrategy;

    /**
     * @psalm-param ?class-string<TResult> $resultClass
     * @psalm-param RelationStrategyInterface<TInner,TKey> $relationStrategy
     * @psalm-param JoinStrategyInterface<TOuter,TInner,TKey,TResult> $joinStrategy
     */
    public function __construct(
        ?string $resultClass,
        RelationStrategyInterface $relationStrategy,
        JoinStrategyInterface $joinStrategy
    ) {
        $this->resultClass = $resultClass;
        $this->relationStrategy = $relationStrategy;
        $this->joinStrategy = $joinStrategy;
    }

    /**
     * @psalm-return RelationStrategyInterface<TInner,TKey>
     */
    public function getRelationStrategy(): RelationStrategyInterface
    {
        return $this->relationStrategy;
    }

    /**
     * @psalm-return JoinStrategyInterface<TOuter,TInner,TKey,TResult>
     */
    public function getJoinStrategy(): JoinStrategyInterface
    {
        return $this->joinStrategy;
    }

    /**
     * {@inheritDoc}
     */
    public function getResultClass(): ?string
    {
        return $this->resultClass;
    }

    /**
     * {@inheritDoc}
     */
    public function associate(iterable $outerResult, ?string $outerClass): \Traversable
    {
        $joinStrategy = $this->joinStrategy;

        $outerKeySelector = $joinStrategy->getOuterKeySelector();
        $outerElements = [];
        $outerKeys = [];

        foreach ($outerResult as $outerElement) {
            $outerElements[] = $outerElement;
            $outerKey = $outerKeySelector($outerElement);
            /** @psalm-suppress RedundantConditionGivenDocblockType */
            if ($outerKey !== null) {
                $outerKeys[] = $outerKey;
            }
        }

        if (empty($outerElements)) {
            return new \EmptyIterator();
        }

        if (empty($outerKeys)) {
            return $joinStrategy->join($outerElements, []);
        }

        $innerResult = $this->relationStrategy->getResult(array_unique($outerKeys), $joinStrategy);

        return $joinStrategy->join($outerElements, $innerResult);
    }
}
