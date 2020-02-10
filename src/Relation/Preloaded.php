<?php

declare(strict_types=1);

namespace Emonkak\Orm\Relation;

use Emonkak\Orm\ResultSet\PreloadedResultSet;
use Emonkak\Orm\ResultSet\ResultSetInterface;

class Preloaded implements RelationStrategyInterface
{
    /**
     * @var string
     */
    private $relationKey;

    /**
     * @var string
     */
    private $outerKey;

    /**
     * @var string
     */
    private $innerKey;

    /**
     * @var class-string
     */
    private $innerClass;

    /**
     * @var mixed[]
     */
    private $innerElements;

    /**
     * @param class-string $innerClass
     * @param mixed[] $innerElements
     */
    public function __construct(
        string $relationKey,
        string $outerKey,
        string $innerKey,
        string $innerClass,
        array $innerElements
    ) {
        $this->relationKey = $relationKey;
        $this->outerKey = $outerKey;
        $this->innerKey = $innerKey;
        $this->innerClass = $innerClass;
        $this->innerElements = $innerElements;
    }

    public function getRelationKey(): string
    {
        return $this->relationKey;
    }

    public function getOuterKey(): string
    {
        return $this->outerKey;
    }

    public function getInnerKey(): string
    {
        return $this->innerKey;
    }

    /**
     * @return class-string
     */
    public function getInnerClass(): string
    {
        return $this->innerClass;
    }

    public function getInnerElements(): array
    {
        return $this->innerElements;
    }

    public function getResult(array $outerKeys): ResultSetInterface
    {
        $innerKeySelector = $this->getInnerKeySelector($this->innerClass);
        $outerKeySet = array_flip($outerKeys);

        $filteredElements = [];

        foreach ($this->innerElements as $element) {
            $innerKey = $innerKeySelector($element);
            if (isset($outerKeySet[$innerKey])) {
                $filteredElements[] = $element;
            }
        }

        return new PreloadedResultSet($filteredElements, $this->innerClass);
    }

    public function getOuterKeySelector(?string $outerClass): callable
    {
        return AccessorCreators::createKeySelector($this->outerKey, $outerClass);
    }

    public function getInnerKeySelector(?string $innerClass): callable
    {
        return AccessorCreators::createKeySelector($this->innerKey, $innerClass);
    }

    public function getResultSelector(?string $outerClass, ?string $innerClass): callable
    {
        return AccessorCreators::createKeyAssignee($this->relationKey, $outerClass);
    }
}
