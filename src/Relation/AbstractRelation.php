<?php

namespace Emonkak\Orm\Relation;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\Relation\JoinStrategy\JoinStrategyInterface;
use Emonkak\Orm\ResultSet\FrozenResultSet;
use Emonkak\Orm\ResultSet\ResultSetInterface;
use Emonkak\Orm\SelectBuilder;

abstract class AbstractRelation implements RelationInterface
{
    /**
     * {@inheritDoc}
     */
    public function join(ResultSetInterface $result)
    {
        $outerElements = $result->toArray();
        if (empty($outerElements)) {
            return new \EmptyIterator();
        }

        $outerClass = $result->getClass();
        $outerResult = new FrozenResultSet($outerElements, $outerClass);
        $outerKeySelector = $this->resolveOuterKeySelector($outerClass);
        $outerKeys = array_map($outerKeySelector, $outerElements);

        $innerResult = $this->getResult($outerKeys);
        $innerClass = $innerResult->getClass();
        $innerKeySelector = $this->resolveInnerKeySelector($innerClass);

        $resultSelector = $this->resolveResultSelector($outerClass, $innerClass);

        return $this->doJoin(
            $outerResult,
            $innerResult,
            $outerKeySelector,
            $innerKeySelector,
            $resultSelector
        );
    }

    /**
     * @param mixed[] $outerKeys
     * @return ResultSetInterface
     */
    abstract protected function getResult($outerKeys);

    /**
     * @param ResultSetInterface $outer
     * @param ResultSetInterface $inner
     * @param callable           $outerKeySelector
     * @param callable           $innerKeySelector
     * @param callable           $resultSelector
     * @return Traversable
     */
    abstract protected function doJoin(ResultSetInterface $outer, ResultSetInterface $inner, callable $outerKeySelector, callable $innerKeySelector, callable $resultSelector);

    /**
     * @param string $outerClass
     * @return callable
     */
    abstract protected function resolveOuterKeySelector($outerClass);

    /**
     * @param string $innerClass
     * @return callable
     */
    abstract protected function resolveInnerKeySelector($innerClass);

    /**
     * @param string $outerClass
     * @param string $innerClass
     * @return callable
     */
    abstract protected function resolveResultSelector($outerClass, $innerClass);
}
