<?php

namespace Emonkak\Orm\Relation;

use Emonkak\Database\PDOInterface;
use Emonkak\Orm\Fetcher\FetcherInterface;
use Emonkak\Orm\Relation\JoinStrategy\JoinStrategyInterface;
use Emonkak\Orm\ResultSet\ResultSetInterface;
use Emonkak\Orm\SelectBuilder;

interface StandardRelationInterface extends RelationInterface
{
    /**
     * @return PDOInterface
     */
    public function getPdo();

    /**
     * @return FetcherInterface
     */
    public function getFetcher();

    /**
     * @return SelectBuilder
     */
    public function getBuilder();

    /**
     * @return JoinStrategyInterface
     */
    public function getJoinStrategy();

    /**
     * @param mixed[] $outerKeys
     * @return ResultSetInterface
     */
    public function getResult(array $outerKeys);

    /**
     * @param string $outerClass
     * @return callable
     */
    public function resolveOuterKeySelector($outerClass);

    /**
     * @param string $innerClass
     * @return callable
     */
    public function resolveInnerKeySelector($innerClass);

    /**
     * @param string $outerClass
     * @param string $innerClass
     * @return callable
     */
    public function resolveResultSelector($outerClass, $innerClass);

    /**
     * Adds the relation to this relation.
     *
     * @param RelationInterface $relation
     * @return $this
     */
    public function with(RelationInterface $relation);
}
