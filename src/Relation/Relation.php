<?php

declare(strict_types=1);

namespace Emonkak\Orm\Relation;

use Emonkak\Orm\Relation\JoinStrategy\JoinStrategyInterface;
use Emonkak\Orm\ResultSet\PreloadedResultSet;
use Emonkak\Orm\ResultSet\RelationResultSet;
use Emonkak\Orm\ResultSet\ResultSetInterface;

class Relation implements RelationInterface
{
    /**
     * @var RelationStrategyInterface
     */
    private $relationStrategy;

    /**
     * @var JoinStrategyInterface
     */
    private $joinStrategy;

    /**
     * @var RelationInterface[]
     */
    private $childlen;

    /**
     * @param RelationInterface[] $childlen
     */
    public function __construct(
        RelationStrategyInterface $relationStrategy,
        JoinStrategyInterface $joinStrategy,
        array $childlen = []
    ) {
        $this->relationStrategy = $relationStrategy;
        $this->joinStrategy = $joinStrategy;
        $this->childlen = $childlen;
    }

    public function getRelationStrategy(): RelationStrategyInterface
    {
        return $this->relationStrategy;
    }

    public function getJoinStrategy(): JoinStrategyInterface
    {
        return $this->joinStrategy;
    }

    /**
     * @return RelationInterface[]
     */
    public function getChildRelations(): array
    {
        return $this->childlen;
    }

    public function with(RelationInterface $relation): self
    {
        $childlen = $this->childlen;
        $childlen[] = $relation;
        return new Relation(
            $this->relationStrategy,
            $this->joinStrategy,
            $childlen
        );
    }

    public function associate(ResultSetInterface $result): \Traversable
    {
        $outerClass = $result->getClass();
        $outerKeySelector = $this->relationStrategy->getOuterKeySelector($outerClass);
        $outerElements = [];
        $outerKeys = [];

        foreach ($result as $element) {
            $outerElements[] = $element;
            $outerKey = $outerKeySelector($element);
            if ($outerKey !== null) {
                $outerKeys[] = $outerKey;
            }
        }

        if (empty($outerElements)) {
            return new \EmptyIterator();
        }

        if (empty($outerKeys)) {
            return new \ArrayIterator($outerElements);
        }

        $outerResult = new PreloadedResultSet($outerElements, $outerClass);
        $innerResult = $this->relationStrategy->getResult(array_unique($outerKeys));

        foreach ($this->childlen as $child) {
            $innerResult = new RelationResultSet($innerResult, $child);
        }

        $innerClass = $innerResult->getClass();
        $innerKeySelector = $this->relationStrategy->getInnerKeySelector($innerClass);
        $resultSelector = $this->relationStrategy->getResultSelector($outerClass, $innerClass);

        return $this->joinStrategy->join(
            $outerResult,
            $innerResult,
            $outerKeySelector,
            $innerKeySelector,
            $resultSelector
        );
    }
}
