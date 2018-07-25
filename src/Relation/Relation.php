<?php

namespace Emonkak\Orm\Relation;

use Emonkak\Orm\Relation\JoinStrategy\JoinStrategyInterface;
use Emonkak\Orm\ResultSet\PreloadResultSet;
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
     * @param RelationStrategyInterface $relationStrategy
     * @param JoinStrategyInterface     $joinStrategy
     */
    public function __construct(RelationStrategyInterface $relationStrategy, JoinStrategyInterface $joinStrategy)
    {
        $this->relationStrategy = $relationStrategy;
        $this->joinStrategy = $joinStrategy;
    }

    /**
     * @return RelationStrategyInterface
     */
    public function getRelationStrategy()
    {
        return $this->relationStrategy;
    }

    /**
     * @return JoinStrategyInterface
     */
    public function getJoinStrategy()
    {
        return $this->joinStrategy;
    }

    /**
     * @param RelationInterface $relation
     */
    public function with(RelationInterface $relation)
    {
        return new Relation(
            $this->relationStrategy->with($relation),
            $this->joinStrategy
        );
    }

    /**
     * {@inheritDoc}
     */
    public function associate(ResultSetInterface $result)
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

        $outerResult = new PreloadResultSet($outerElements, $outerClass);
        $innerResult = $this->relationStrategy->getResult($outerKeys);
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
