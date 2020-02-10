<?php

declare(strict_types=1);

namespace Emonkak\Orm\Relation;

use Emonkak\Orm\ResultSet\ResultSetInterface;

interface RelationStrategyInterface
{
    /**
     * @param mixed[] $outerKeys
     */
    public function getResult(array $outerKeys): ResultSetInterface;

    /**
     * @param ?class-string $outerClass
     * @return callable(mixed):mixed
     */
    public function getOuterKeySelector(?string $outerClass): callable;

    /**
     * @param ?class-string $innerClass
     * @return callable(mixed):mixed
     */
    public function getInnerKeySelector(?string $innerClass): callable;

    /**
     * @param ?class-string $outerClass
     * @param ?class-string $innerClass
     * @return callable(mixed,mixed):mixed
     */
    public function getResultSelector(?string $outerClass, ?string $innerClass): callable;
}
