<?php

namespace Emonkak\Orm\Relation;

use Emonkak\Orm\ResultSet\PreloadResultSet;
use Emonkak\Orm\ResultSet\ResultSetInterface;

abstract class AbstractStandardRelation implements StandardRelationInterface
{
    /**
     * {@inheritDoc}
     */
    public function associate(ResultSetInterface $result)
    {
        $outerClass = $result->getClass();
        $outerKeySelector = $this->resolveOuterKeySelector($outerClass);
        $outerElements = [];
        $outerKeys = [];

        foreach ($result as $element) {
            $outerElements[] = $element;
            $outerKeys[] = $outerKeySelector($element);
        }

        if (empty($outerElements)) {
            return new \EmptyIterator();
        }

        $outerResult = new PreloadResultSet($outerElements, $outerClass);
        $innerResult = $this->getResult($outerKeys);
        $innerClass = $innerResult->getClass();
        $innerKeySelector = $this->resolveInnerKeySelector($innerClass);
        $resultSelector = $this->resolveResultSelector($outerClass, $innerClass);

        $joinStrategy = $this->getJoinStrategy();

        return $joinStrategy->join(
            $outerResult,
            $innerResult,
            $outerKeySelector,
            $innerKeySelector,
            $resultSelector
        );
    }
}
