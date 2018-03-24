<?php

namespace Emonkak\Orm\Relation;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\Relation\JoinStrategy\JoinStrategyInterface;
use Emonkak\Orm\ResultSet\ResultSetInterface;
use Emonkak\Orm\SelectBuilder;

interface RelationStrategyInterface
{
    /**
     * @param mixed[] $outerKeys
     * @return ResultSetInterface
     */
    public function getResult(array $outerKeys);

    /**
     * @param string $outerClass
     * @return callable
     */
    public function getOuterKeySelector($outerClass);

    /**
     * @param string $innerClass
     * @return callable
     */
    public function getInnerKeySelector($innerClass);

    /**
     * @param string $outerClass
     * @param string $innerClass
     * @return callable
     */
    public function getResultSelector($outerClass, $innerClass);

    /**
     * @param RelationInterface $relation
     * @return $this
     */
    public function with(RelationInterface $relation);
}
