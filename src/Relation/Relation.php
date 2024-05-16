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
     * @var ?class-string
     */
    private ?string $resultClass;

    /**
     * @var RelationStrategyInterface<TInner,TKey>
     */
    private RelationStrategyInterface $relationStrategy;

    /**
     * @var JoinStrategyInterface<TOuter,TInner,TKey,TResult>
     */
    private JoinStrategyInterface $joinStrategy;

    /**
     * @param ?class-string $resultClass
     * @param RelationStrategyInterface<TInner,TKey> $relationStrategy
     * @param JoinStrategyInterface<TOuter,TInner,TKey,TResult> $joinStrategy
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
     * @return RelationStrategyInterface<TInner,TKey>
     */
    public function getRelationStrategy(): RelationStrategyInterface
    {
        return $this->relationStrategy;
    }

    /**
     * @return JoinStrategyInterface<TOuter,TInner,TKey,TResult>
     */
    public function getJoinStrategy(): JoinStrategyInterface
    {
        return $this->joinStrategy;
    }

    public function getResultClass(): ?string
    {
        return $this->resultClass;
    }

    public function associate(iterable $outerResult, ?string $outerClass): \Traversable
    {
        $joinStrategy = $this->joinStrategy;

        $outerKeySelector = $joinStrategy->getOuterKeySelector();
        $outerElements = [];
        $outerKeys = [];

        foreach ($outerResult as $outerElement) {
            $outerElements[] = $outerElement;
            $outerKey = $outerKeySelector($outerElement);
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
