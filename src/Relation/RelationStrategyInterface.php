<?php

namespace Emonkak\Orm\Relation;

use Emonkak\Orm\ResultSet\ResultSetInterface;

interface RelationStrategyInterface
{
    /**
     * @param mixed[] $outerKeys
     * @return ResultSetInterface
     */
    public function getResult(array $outerKeys);

    /**
     * @param ?string $outerClass
     * @return callable
     */
    public function getOuterKeySelector($outerClass);

    /**
     * @param ?string $innerClass
     * @return callable
     */
    public function getInnerKeySelector($innerClass);

    /**
     * @param ?string $outerClass
     * @param ?string $innerClass
     * @return callable
     */
    public function getResultSelector($outerClass, $innerClass);
}
